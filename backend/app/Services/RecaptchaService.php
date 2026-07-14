<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    public function verify(string $token): bool
    {
        $secret = config('services.recaptcha.secret');

        if (empty($secret)) {
            Log::error('RECAPTCHA_SECRET_KEY não configurado');

            return false;
        }

        try {
            $response = Http::timeout(5)
                ->asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                ]);

            if (!$response->successful()) {
                Log::warning('reCAPTCHA Google API HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            if (empty($data['success'])) {
                Log::warning('reCAPTCHA verification unsuccessful', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'hostname' => $data['hostname'] ?? null,
                ]);

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
