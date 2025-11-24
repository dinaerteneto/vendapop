<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Tenant;
use App\Models\TenantSocial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_slug' => 'required|string|max:255|unique:tenants,slug',
            'whatsapp_number' => 'required|string',
            'email' => 'required|email|max:255|unique:users,email',
            'recaptcha_token' => 'required|string',
        ]);

        // Verify reCAPTCHA v3
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'); // Test secret for development
        $recaptchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $validated['recaptcha_token']);
        $recaptchaData = json_decode($recaptchaResponse, true);

        // reCAPTCHA v3 returns a score (0.0 to 1.0)
        // Score < 0.5 is considered suspicious
        $score = $recaptchaData['score'] ?? 0;
        
        if (!$recaptchaData['success'] || $score < 0.5) {
            return response()->json([
                'message' => 'Verificação reCAPTCHA falhou. Tente novamente.',
                'errors' => ['recaptcha' => ['Verificação reCAPTCHA falhou.']]
            ], 422);
        }

        // Create Tenant
        $tenant = Tenant::create([
            'name' => $validated['store_name'],
            'slug' => Str::slug($validated['store_slug']),
            'whatsapp_number' => $validated['whatsapp_number'],
            'primary_color' => '#7c3aed', // Default purple
            'secondary_color' => '#f3e8ff',
        ]);

        // Generate random password
        $generatedPassword = Str::random(12);

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
        ], 201);
    }
}

