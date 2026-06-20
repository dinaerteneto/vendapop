<?php

namespace App\Domain\Payment;

class RefundRequest
{
    public function __construct(
        public readonly string $transaction_id,
        public readonly ?float $amount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transaction_id: $data['transaction_id'],
            amount: $data['amount'] ?? null,
        );
    }
}
