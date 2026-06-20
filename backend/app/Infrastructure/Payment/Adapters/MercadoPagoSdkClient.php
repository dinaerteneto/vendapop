<?php

namespace App\Infrastructure\Payment\Adapters;

interface MercadoPagoSdkClient
{
    public function createPreference(array $data): object;
    public function getPayment(int $id): object;
    public function refundTotal(int $paymentId): void;
    public function refundPartial(int $paymentId, float $amount): void;
}
