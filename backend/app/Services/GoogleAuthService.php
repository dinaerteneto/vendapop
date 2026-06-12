<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class GoogleAuthService
{
    public function getRedirectUrl(): string
    {
        return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
    }

    public function handleCallback(): SocialiteUser
    {
        return Socialite::driver('google')->stateless()->user();
    }

    public function findOrCreateUser(SocialiteUser $googleUser): array
    {
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            return [
                'status' => 'new_user',
                'google_user' => $googleUser,
            ];
        }

        if ($user->email_verified_at) {
            $user->google_id = $googleUser->id;
            $user->google_token = $googleUser->token;
            $user->google_refresh_token = $googleUser->refreshToken;
            $user->save();

            return [
                'status' => 'verified',
                'user' => $user,
            ];
        }

        return [
            'status' => 'link_required',
            'user' => $user,
            'google_user' => $googleUser,
        ];
    }

    public function linkToExistingUser(User $user, SocialiteUser $googleUser): User
    {
        $user->google_id = $googleUser->id;
        $user->google_token = $googleUser->token;
        $user->google_refresh_token = $googleUser->refreshToken;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    public function createFromGoogleUser(SocialiteUser $googleUser, array $onboardingData): User
    {
        $tenant = Tenant::create([
            'name' => $onboardingData['store_name'],
            'slug' => Str::slug($onboardingData['store_slug']),
            'whatsapp_number' => $onboardingData['whatsapp_number'],
            'primary_color' => '#7c3aed',
            'secondary_color' => '#f3e8ff',
        ]);

        $generatedPassword = Str::random(12);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $googleUser->name ?? 'Admin',
            'email' => $googleUser->email,
            'password' => Hash::make($generatedPassword),
            'is_owner' => true,
            'email_verified_at' => now(),
            'google_id' => $googleUser->id,
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);

        return $user;
    }
}
