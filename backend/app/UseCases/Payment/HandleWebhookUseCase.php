<?php

namespace App\UseCases\Payment;

use App\Domain\Payment\PaymentNotification;
use App\Services\PaymentService;

class HandleWebhookUseCase
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function execute(PaymentNotification $notification): void
    {
        $this->paymentService->handleNotification($notification);
    }
}
