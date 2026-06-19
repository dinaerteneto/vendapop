<?php

namespace App\Domain\Payment;

class CheckoutResponse
{
    public function __construct(
        public readonly string $checkout_url,
        public readonly string $transaction_id,
        public readonly ?\DateTime $expires_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            checkout_url: $data['checkout_url'],
            transaction_id: $data['transaction_id'],
            expires_at: isset($data['expires_at']) ? new \DateTime($data['expires_at']) : null,
        );
    }
}
