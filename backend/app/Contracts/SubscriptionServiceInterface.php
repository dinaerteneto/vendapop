<?php

namespace App\Contracts;

use App\Models\Invite;
use App\Models\Subscription;
use App\Models\Tenant;

interface SubscriptionServiceInterface
{
    public function createFromInvite(Tenant $tenant, Invite $invite): Subscription;
    public function getActive(Tenant $tenant): ?Subscription;
    public function isActive(Tenant $tenant): bool;
    public function expiresInDays(Tenant $tenant): ?int;
    public function expireTrials(): void;
}
