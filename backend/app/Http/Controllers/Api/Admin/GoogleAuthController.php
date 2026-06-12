<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    public function __construct(
        private GoogleAuthService $googleAuthService
    ) {}

    public function redirect()
    {
        return redirect($this->googleAuthService->getRedirectUrl());
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = $this->googleAuthService->handleCallback();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google auth error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
            return redirect($frontendUrl . '/admin/auth/google/callback?error=google_auth_failed');
        }

        $result = $this->googleAuthService->findOrCreateUser($googleUser);
        $frontendUrl = config('services.frontend_url', 'http://localhost:5173');

        return match ($result['status']) {
            'new_user' => redirect($frontendUrl . '/admin/onboarding?' . http_build_query([
                'temporary_token' => $this->buildTemporaryToken($result['google_user']),
                'email' => $result['google_user']->email,
                'name' => $result['google_user']->name,
            ])),
            'verified' => redirect($frontendUrl . '/admin/auth/google/callback?' . http_build_query([
                'status' => 'verified',
                'token' => $result['user']->createToken('admin_token')->plainTextToken,
                'tenant_slug' => $result['user']->tenant->slug,
                'user_name' => $result['user']->name,
                'user_email' => $result['user']->email,
            ])),
            'link_required' => redirect($frontendUrl . '/admin/auth/google/callback?' . http_build_query([
                'status' => 'link_required',
                'email' => $result['user']->email,
                'google_id' => $result['google_user']->id,
                'google_token' => $result['google_user']->token,
                'google_refresh_token' => $result['google_user']->refreshToken,
            ])),
        };
    }

    public function link(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'google_id' => 'required|string',
            'google_token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Usuário não encontrado.'],
            ]);
        }

        $this->googleAuthService->linkToExistingUser($user, (object) [
            'id' => $request->google_id,
            'token' => $request->google_token,
            'refreshToken' => $request->input('google_refresh_token'),
        ]);

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'tenant_slug' => $user->tenant->slug,
        ]);
    }

    public function onboarding(Request $request)
    {
        $request->validate([
            'temporary_token' => 'required|string',
            'store_name' => 'required|string|max:255',
            'store_slug' => 'required|string|max:255|unique:tenants,slug',
            'whatsapp_number' => 'required|string',
        ]);

        try {
            $googleData = Crypt::decryptString($request->temporary_token);
            $googleData = json_decode($googleData, true);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'temporary_token' => ['Token inválido ou expirado.'],
            ]);
        }

        $googleUser = (object) $googleData;

        $user = $this->googleAuthService->createFromGoogleUser($googleUser, $request->all());

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'tenant_slug' => $user->tenant->slug,
        ]);
    }

    private function buildTemporaryToken($googleUser): string
    {
        return Crypt::encryptString(json_encode([
            'id' => $googleUser->id,
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'token' => $googleUser->token,
            'refresh_token' => $googleUser->refreshToken,
        ]));
    }
}
