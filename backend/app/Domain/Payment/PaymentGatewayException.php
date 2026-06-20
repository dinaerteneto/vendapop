<?php

namespace App\Domain\Payment;

class PaymentGatewayException extends \RuntimeException
{
    public static function missingCredentials(): self
    {
        return new self('Payment gateway credentials are not configured.');
    }

    public static function apiError(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, 0, $previous);
    }

    public static function invalidPlan(string $planType): self
    {
        return new self("Invalid plan type: {$planType}");
    }
}
