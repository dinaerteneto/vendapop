<?php

namespace App\Infrastructure\Payment\Adapters;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Payment\PaymentRefundClient;
use MercadoPago\Client\Preference\PreferenceClient;

class MercadoPagoSdkClientImpl implements MercadoPagoSdkClient
{
    private PreferenceClient $preferenceClient;
    private PaymentClient $paymentClient;
    private PaymentRefundClient $refundClient;

    public function __construct()
    {
        $this->preferenceClient = new PreferenceClient();
        $this->paymentClient = new PaymentClient();
        $this->refundClient = new PaymentRefundClient();
    }

    public function createPreference(array $data): object
    {
        return $this->preferenceClient->create($data);
    }

    public function getPayment(int $id): object
    {
        return $this->paymentClient->get($id);
    }

    public function refundTotal(int $paymentId): void
    {
        $this->refundClient->refundTotal($paymentId);
    }

    public function refundPartial(int $paymentId, float $amount): void
    {
        $this->refundClient->refund($paymentId, $amount);
    }
}
