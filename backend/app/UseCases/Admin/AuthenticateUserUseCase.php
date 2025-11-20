<?php

namespace App\UseCases\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateUserUseCase
{
    public function execute(string $email, string $password): array
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new \Exception('Invalid credentials');
        }

        $user = Auth::user();

        if (!$user instanceof User) {
            throw new \Exception('User not found');
        }

        $token = $user->createToken('admin_token');

        return [
            'token' => $token->plainTextToken,
            'user' => $user,
            'tenant_slug' => $user->tenant->slug,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
