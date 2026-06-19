<?php

namespace App\Domain\Payment;

class PaymentNotification
{
    public function __construct(
        public readonly string $transaction_id,
        public readonly string $status,
        public readonly ?string $external_reference = null,
        public readonly ?\DateTime $paid_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transaction_id: $data['transaction_id'],
            status: $data['status'],
            external_reference: $data['external_reference'] ?? null,
            paid_at: isset($data['paid_at']) ? new \DateTime($data['paid_at']) : null,
        );
    }
}
