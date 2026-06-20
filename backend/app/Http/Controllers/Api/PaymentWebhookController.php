<?php

namespace App\Http\Controllers\Api;

use App\Domain\Payment\PaymentNotification;
use App\Http\Controllers\Controller;
use App\UseCases\Payment\HandleWebhookUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private HandleWebhookUseCase $handleWebhookUseCase,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (!$this->isValidWebhook($payload)) {
            Log::warning('Invalid payment webhook payload', ['gateway' => 'mercadopago']);
            return response()->json(['message' => 'Invalid webhook payload'], 422);
        }

        $gatewayPaymentId = $payload['data']['id'];

        Log::debug('Payment webhook received', [
            'gateway' => 'mercadopago',
            'gateway_payment_id' => $gatewayPaymentId,
        ]);

        $notification = new PaymentNotification(
            transaction_id: $gatewayPaymentId,
            status: 'pending',
        );

        $this->handleWebhookUseCase->execute($notification);

        return response()->json(['status' => 'accepted'], 200);
    }

    private function isValidWebhook(array $payload): bool
    {
        $type = $payload['type'] ?? null;
        $action = $payload['action'] ?? null;

        $validType = in_array($type, ['payment', 'merchant_order'], true);
        $validAction = $action !== null && str_starts_with((string) $action, 'payment.');

        if (!$validType && !$validAction) {
            return false;
        }

        return isset($payload['data']['id']);
    }
}
