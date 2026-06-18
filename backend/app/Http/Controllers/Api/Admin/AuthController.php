<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        // Check if email is verified (skip in local/testing env)
        if (!app()->environment('local') && !$user->email_verified_at) {
            return response()->json([
                'message' => 'E-mail não verificado. Verifique seu e-mail para ativar sua conta.',
                'email_not_verified' => true,
            ], 403);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('admin_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
                'tenant_slug' => $user->tenant->slug,
                'tenant' => [
                    'slug' => $user->tenant->slug,
                    'onboarding_completed' => $user->tenant->onboarding_completed,
                    'onboarding_step' => $user->tenant->onboarding_step,
                ],
            ]);
        }

        return response()->json(['message' => 'Credenciais inválidas'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'A senha atual está incorreta.',
                'errors' => ['current_password' => ['A senha atual está incorreta.']]
            ], 422);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }
}

