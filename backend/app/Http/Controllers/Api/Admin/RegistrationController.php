<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\SpotServiceInterface;
use App\Exceptions\SpotExhaustedException;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Invite;
use App\Models\SpotBatch;
use App\Models\Tenant;
use App\Models\TenantSocial;
use App\Models\User;
use App\Services\DemoDataService;
use App\Services\InviteService;
use App\Services\RecaptchaService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function __construct(
        private InviteService $inviteService,
        private SubscriptionService $subscriptionService,
        private SpotServiceInterface $spotService,
    ) {}

    public function store(Request $request)
    {
        $rules = [
            'store_name' => 'required|string|max:255',
            'store_slug' => 'required|string|max:255|unique:tenants,slug',
            'whatsapp_number' => 'required|string',
            'email' => 'required|email|max:255|unique:users,email',
            'terms_accepted' => 'required|accepted',
            'invite_code' => 'nullable|string|max:8',
        ];

        if (!app()->environment('local')) {
            $rules['recaptcha_token'] = 'required|string';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        $validated = $request->validate($rules);

        // Verify reCAPTCHA v3 (skip in local environment)
        if (!app()->environment('local')) {
            if (!app(RecaptchaService::class)->verify($validated['recaptcha_token'])) {
                return response()->json([
                    'message' => 'Verificação reCAPTCHA falhou. Tente novamente.',
                    'errors' => ['recaptcha' => ['Verificação reCAPTCHA falhou.']]
                ], 422);
            }
        }

        // Create Tenant in a transaction with invite validation and spot consumption
        try {
            $invite = null;

            $tenant = DB::transaction(function () use ($validated, &$invite) {
                // Validate invite code if provided
                if (!empty($validated['invite_code'])) {
                    $invite = $this->inviteService->validate($validated['invite_code']);
                }

                // Spot consumption check (only if spot system has batches)
                if (!$invite && SpotBatch::exists() && !$this->spotService->consume()) {
                    throw new SpotExhaustedException();
                }

                $tenant = Tenant::create([
                    'name' => $validated['store_name'],
                    'slug' => Str::slug($validated['store_slug']),
                    'whatsapp_number' => $validated['whatsapp_number'],
                    'primary_color' => '#7c3aed',
                    'secondary_color' => '#f3e8ff',
                ]);

                if ($invite) {
                    $this->subscriptionService->createFromInvite($tenant, $invite);
                    $this->inviteService->consume($invite, $tenant);
                }

                app(DemoDataService::class)->seedFor($tenant);

                return $tenant;
            });
        } catch (SpotExhaustedException $e) {
            return response()->json([
                'message' => 'Vagas esgotadas no momento.',
                'redirect_to' => 'waitlist',
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        // Generate random password (or use provided one in local env)
        $generatedPassword = !empty($validated['password']) ? $validated['password'] : Str::random(12);

        // Generate email verification token
        $verificationToken = Str::random(64);

        // Store verification token
        DB::table('email_verifications')->insert([
            'email' => $validated['email'],
            'token' => Hash::make($verificationToken),
            'created_at' => now(),
        ]);

        // Create Admin User (email not verified yet)
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => $validated['email'],
            'password' => Hash::make($generatedPassword),
            'is_owner' => true,
            'email_verified_at' => null,
            'terms_accepted_at' => now(),
        ]);

        // Create default social networks with icons
        $socialNetworks = [
            [
                'name' => 'Instagram',
                'url' => '',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/2111/2111463.png',
            ],
            [
                'name' => 'TikTok',
                'url' => '',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/3046/3046120.png',
            ],
            [
                'name' => 'YouTube',
                'url' => '',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/1384/1384060.png',
            ],
            [
                'name' => 'Facebook',
                'url' => '',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/733/733547.png',
            ],
        ];

        foreach ($socialNetworks as $social) {
            TenantSocial::create([
                'tenant_id' => $tenant->id,
                'name' => $social['name'],
                'url' => $social['url'],
                'icon' => $social['icon'],
            ]);
        }

        // Send welcome email with verification link and password
        $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
        $verificationUrl = $frontendUrl . '/admin/verify-email?token=' . $verificationToken . '&email=' . urlencode($user->email);

        try {
            Mail::to($user->email)->send(new WelcomeMail($user, $verificationUrl, $generatedPassword));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de boas-vindas: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Loja criada com sucesso! Verifique seu e-mail para ativar sua conta e receber sua senha.',
            'plan_type' => $tenant->subscriptions()->latest()->first()?->plan_type ?? 'free',
            'trial_ends_at' => $tenant->subscriptions()->latest()->first()?->ends_at,
        ], 201);
    }
}

