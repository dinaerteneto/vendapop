<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    public function verify(string $token): bool
    {
        $secret = env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

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
            $score = $data['score'] ?? 0;

            if (empty($data['success'])) {
                Log::warning('reCAPTCHA verification unsuccessful', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'score' => $score,
                    'hostname' => $data['hostname'] ?? null,
                ]);

                return false;
            }

            if ($score < 0.5) {
                Log::info('reCAPTCHA low score', [
                    'score' => $score,
                    'action' => $data['action'] ?? null,
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
