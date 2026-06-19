<?php

namespace App\Domain\Payment;

class RefundResponse
{
    public function __construct(
        public readonly string $transaction_id,
        public readonly bool $refunded,
        public readonly ?\DateTime $refunded_at = null,
        public readonly ?float $amount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transaction_id: $data['transaction_id'],
            refunded: $data['refunded'],
            refunded_at: isset($data['refunded_at']) ? new \DateTime($data['refunded_at']) : null,
            amount: $data['amount'] ?? null,
        );
    }
}
