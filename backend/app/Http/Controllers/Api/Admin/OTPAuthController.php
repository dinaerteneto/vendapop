<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPAuthService;
use App\Services\RecaptchaService;
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
            if (!app(RecaptchaService::class)->verify($request->recaptcha_token)) {
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
