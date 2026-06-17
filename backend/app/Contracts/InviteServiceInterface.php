<?php

namespace App\Contracts;

use App\Models\Invite;
use App\Models\Tenant;

interface InviteServiceInterface
{
    public function generateManual(Tenant $creator, int $count = 1): array;
    public function createPublicLink(int $maxUses, ?int $expiresInHours = 48): Invite;
    public function validate(string $code): Invite;
    public function consume(Invite $invite, Tenant $tenant): void;
    public function remainingForTenant(Tenant $tenant): int;
}
