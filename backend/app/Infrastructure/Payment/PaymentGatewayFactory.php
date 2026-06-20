<?php

namespace App\Infrastructure\Payment;

use App\Domain\Payment\PaymentGateway;
use App\Domain\Payment\PaymentGatewayException;
use App\Infrastructure\Payment\Adapters\MercadoPagoAdapter;

class PaymentGatewayFactory
{
    public function make(?string $gateway = null): PaymentGateway
    {
        $gateway = strtolower($gateway ?? config('services.payment.gateway', 'mercadopago'));

        return match ($gateway) {
            'mercadopago' => new MercadoPagoAdapter(),
            default => throw new PaymentGatewayException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
