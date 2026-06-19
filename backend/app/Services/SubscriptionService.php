<?php

namespace App\Services;

use App\Contracts\SubscriptionServiceInterface;
use App\Models\Invite;
use App\Models\Subscription;
use App\Models\Tenant;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function createFromInvite(Tenant $tenant, Invite $invite): Subscription
    {
        // Determine source
        if ($invite->type === 'public') {
            $source = 'public_link';
        } elseif ($invite->created_by_tenant_id === null) {
            $source = 'manual';
        } else {
            $source = 'founder';
        }

        // Determine duration
        $isLifetime = ($source === 'manual');
        $endsAt = $isLifetime ? null : now()->addDays(config('trial.duration_days'));

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => $isLifetime ? 'active' : 'trial',
            'invite_id' => $invite->id,
            'invite_source' => $source,
            'started_at' => now(),
            'ends_at' => $endsAt,
        ]);
    }

    public function getActive(Tenant $tenant): ?Subscription
    {
        return Subscription::where('tenant_id', $tenant->id)
            ->whereIn('plan_status', ['active', 'trial'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function isActive(Tenant $tenant): bool
    {
        $subscription = $this->getActive($tenant);

        if (!$subscription) {
            return false;
        }

        if ($subscription->plan_status === 'active' && $subscription->ends_at === null) {
            return true;
        }

        if ($subscription->plan_status === 'trial' && !$subscription->isExpired()) {
            return true;
        }

        return false;
    }

    public function expiresInDays(Tenant $tenant): ?int
    {
        $subscription = $this->getActive($tenant);

        if (!$subscription) {
            return null;
        }

        return $subscription->daysRemaining();
    }

    public function expireTrials(): void
    {
        Subscription::where('plan_status', 'trial')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->update(['plan_status' => 'expired']);
    }
}
