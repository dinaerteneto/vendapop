<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if email exists for security
            return response()->json([
                'message' => 'Se o e-mail existir, um link de redefinição será enviado.'
            ], 200);
        }

        // Generate token
        $token = Str::random(64);

        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send email with reset link
        $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
        $resetUrl = $frontendUrl . '/admin/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));

            return response()->json([
                'message' => 'Se o e-mail existir, um link de redefinição será enviado.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de reset de senha: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao enviar email. Tente novamente mais tarde.'
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['E-mail não encontrado.'],
            ]);
        }

        // Check token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            throw ValidationException::withMessages([
                'token' => ['Token inválido ou expirado.'],
            ]);
        }

        // Check if token is valid (not expired - 60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'token' => ['Token expirado. Solicite um novo link.'],
            ]);
        }

        // Verify token
        if (!Hash::check($request->token, $passwordReset->token)) {
            throw ValidationException::withMessages([
                'token' => ['Token inválido.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Senha redefinida com sucesso!'
        ], 200);
    }
}

