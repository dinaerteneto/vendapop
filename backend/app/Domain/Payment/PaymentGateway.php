<?php

namespace App\Domain\Payment;

interface PaymentGateway
{
    public function createCheckout(CreateCheckoutRequest $request): CheckoutResponse;

    public function processNotification(PaymentNotification $notification): PaymentNotification;

    public function refund(RefundRequest $request): RefundResponse;
}
