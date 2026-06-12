<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OTPAuthController extends Controller
{
    public function __construct(
        private OTPAuthService $otpAuthService
    ) {}

    public function send(Request $request)
    {
        $rules = ['email' => 'required|email'];

        if (!app()->environment('local')) {
            $rules['recaptcha_token'] = 'required|string';
        }

        $request->validate($rules);

        // Verify reCAPTCHA v3 (skip in local environment)
        if (!app()->environment('local')) {
            $recaptchaSecret = env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
            $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $request->recaptcha_token;
            $context = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
            $recaptchaResponse = @file_get_contents($verifyUrl, false, $context);
            $recaptchaData = $recaptchaResponse ? json_decode($recaptchaResponse, true) : null;
            $score = $recaptchaData['score'] ?? 0;

            if (!$recaptchaData || empty($recaptchaData['success']) || $score < 0.5) {
                return response()->json([
                    'message' => 'Verificação reCAPTCHA falhou. Tente novamente.',
                    'errors' => ['recaptcha' => ['Verificação reCAPTCHA falhou.']]
                ], 422);
            }
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Se o e-mail existir, um código será enviado.'
            ], 200);
        }

        $this->otpAuthService->generateAndSend($request->email);

        return response()->json([
            'message' => 'Código enviado para seu e-mail.',
        ], 200);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        try {
            $user = $this->otpAuthService->verifyOtp($request->email, $request->code);
        } catch (ValidationException $e) {
            throw $e;
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'tenant_slug' => $user->tenant->slug,
        ]);
    }

    public function magicLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        try {
            $user = $this->otpAuthService->verifyMagicLink($request->email, $request->token);
        } catch (ValidationException $e) {
            throw $e;
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'tenant_slug' => $user->tenant->slug,
        ]);
    }
}
