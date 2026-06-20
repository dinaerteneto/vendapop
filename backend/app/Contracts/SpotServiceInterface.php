<?php

namespace App\Contracts;

use App\Models\SpotBatch;

interface SpotServiceInterface
{
    public function consume(): bool;
    public function remaining(): int;
    public function replenish(): void;
    public function resetMonth(): void;
}
