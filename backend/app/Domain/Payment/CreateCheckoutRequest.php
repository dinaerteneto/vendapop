<?php

namespace App\Domain\Payment;

class CreateCheckoutRequest
{
    public function __construct(
        public readonly string $plan_type,
        public readonly int|string $tenant_id,
        public readonly string $return_url,
        public readonly string $cancel_url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plan_type: $data['plan_type'],
            tenant_id: $data['tenant_id'],
            return_url: $data['return_url'],
            cancel_url: $data['cancel_url'],
        );
    }
}
