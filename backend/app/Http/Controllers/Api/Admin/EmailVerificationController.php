<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['E-mail não encontrado.'],
            ]);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'E-mail já verificado.',
                'verified' => true,
            ], 200);
        }

        // Check token
        $verification = DB::table('email_verifications')
            ->where('email', $request->email)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$verification) {
            throw ValidationException::withMessages([
                'token' => ['Token inválido ou expirado.'],
            ]);
        }

        // Check if token is valid (not expired - 24 hours)
        if (now()->diffInHours($verification->created_at) > 24) {
            DB::table('email_verifications')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'token' => ['Token expirado. Solicite um novo link.'],
            ]);
        }

        // Verify token
        if (!Hash::check($request->token, $verification->token)) {
            throw ValidationException::withMessages([
                'token' => ['Token inválido.'],
            ]);
        }

        // Verify email
        $user->email_verified_at = now();
        $user->save();

        // Delete verification token
        DB::table('email_verifications')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'E-mail verificado com sucesso!',
            'verified' => true,
        ], 200);
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if email exists for security
            return response()->json([
                'message' => 'Se o e-mail existir, um novo link será enviado.'
            ], 200);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'E-mail já verificado.',
            ], 200);
        }

        // Generate new token
        $verificationToken = \Illuminate\Support\Str::random(64);

        // Store verification token
        DB::table('email_verifications')->insert([
            'email' => $user->email,
            'token' => Hash::make($verificationToken),
            'created_at' => now(),
        ]);

        // Send welcome email with verification link
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $verificationUrl = $frontendUrl . '/admin/verify-email?token=' . $verificationToken . '&email=' . urlencode($user->email);

        // Generate new password for resend
        $generatedPassword = \Illuminate\Support\Str::random(12);
        $user->password = \Illuminate\Support\Facades\Hash::make($generatedPassword);
        $user->save();

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeMail($user, $verificationUrl, $generatedPassword));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao reenviar email de verificação: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Se o e-mail existir, um novo link será enviado.'
        ], 200);
    }
}

