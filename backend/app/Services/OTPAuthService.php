<?php

namespace App\Services;

use App\Mail\OTPMail;
use App\Models\OtpToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OTPAuthService
{
    public function generateAndSend(string $email): void
    {
        // Generate 6-digit OTP
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Generate magic link token
        $magicLinkToken = Str::random(64);

        // Store in database (hashed)
        OtpToken::create([
            'email' => $email,
            'code' => Hash::make($otpCode),
            'magic_link_token' => Hash::make($magicLinkToken),
            'expires_at' => now()->addMinutes(30),
        ]);

        // Build magic link URL
        $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
        $magicLinkUrl = $frontendUrl . '/admin/magic-login?email=' . urlencode($email) . '&token=' . $magicLinkToken;

        // Send email
        try {
            Mail::to($email)->send(new OTPMail($email, $otpCode, $magicLinkUrl));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email OTP: ' . $e->getMessage());
        }
    }

    public function verifyOtp(string $email, string $code): User
    {
        $otpToken = OtpToken::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->whereNotNull('code')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpToken || !Hash::check($code, $otpToken->code)) {
            throw ValidationException::withMessages([
                'code' => ['Código inválido ou expirado.'],
            ]);
        }

        $otpToken->used_at = now();
        $otpToken->save();

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Usuário não encontrado.'],
            ]);
        }

        return $user;
    }

    public function verifyMagicLink(string $email, string $token): User
    {
        $otpToken = OtpToken::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->whereNotNull('magic_link_token')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpToken || !Hash::check($token, $otpToken->magic_link_token)) {
            throw ValidationException::withMessages([
                'token' => ['Link inválido ou expirado.'],
            ]);
        }

        $otpToken->used_at = now();
        $otpToken->save();

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Usuário não encontrado.'],
            ]);
        }

        return $user;
    }
}
