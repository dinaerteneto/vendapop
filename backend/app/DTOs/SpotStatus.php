<?php

namespace App\DTOs;

class SpotStatus
{
    public function __construct(
        public readonly int $remaining,
        public readonly int $total,
        public readonly ?string $nextReplenish,
    ) {}
}
