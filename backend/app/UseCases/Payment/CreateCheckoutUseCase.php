<?php

namespace App\UseCases\Payment;

use App\Domain\Payment\CheckoutResponse;
use App\Models\Subscription;
use App\Services\PaymentService;

class CreateCheckoutUseCase
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function execute(int $subscriptionId, string $planType, string $returnUrl, string $cancelUrl): CheckoutResponse
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        return $this->paymentService->createCheckout(
            subscription: $subscription,
            planType: $planType,
            returnUrl: $returnUrl,
            cancelUrl: $cancelUrl,
        );
    }
}
