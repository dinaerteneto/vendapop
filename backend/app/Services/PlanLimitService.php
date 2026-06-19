<?php

namespace App\Services;

use App\Models\PlanLimit;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class PlanLimitService
{
    public function getLimits(string $planType): ?PlanLimit
    {
        return Cache::remember(
            $this->cacheKey($planType),
            config('plan-limits.cache_ttl', 3600),
            fn() => PlanLimit::byPlanType($planType)->first()
        );
    }

    public function getLimit(string $planType): ?int
    {
        $limit = $this->getLimits($planType)?->max_products;

        if ($limit === 0) {
            return null;
        }

        return $limit;
    }

    public function canAddProducts(string $planType, int $currentCount): bool
    {
        $planLimit = $this->getLimits($planType);

        if (!$planLimit) {
            return true;
        }

        return $planLimit->canAddMoreProducts($currentCount);
    }

    public function canAddCategories(string $planType, ?int $currentCount): bool
    {
        $planLimit = $this->getLimits($planType);

        if (!$planLimit) {
            return true;
        }

        return $planLimit->canAddMoreCategories($currentCount);
    }

    public function canUseFeature(string $planType, string $feature): bool
    {
        $planLimit = $this->getLimits($planType);

        if (!$planLimit) {
            return false;
        }

        return (bool) ($planLimit->{$feature} ?? false);
    }

    public function countActiveProducts(Tenant $tenant): int
    {
        return $tenant->products()->where('is_active', true)->count();
    }

    public function resolvePlanType(Tenant $tenant): string
    {
        $subscription = $tenant->subscriptions()
            ->whereIn('plan_status', ['active', 'trial'])
            ->latest()
            ->first();

        return $subscription?->plan_type ?? 'free';
    }

    public function checkLimit(Tenant $tenant): ?int
    {
        $planType = $this->resolvePlanType($tenant);
        $limit = $this->getLimit($planType);

        if ($limit === null) {
            return null;
        }

        $count = $this->countActiveProducts($tenant);

        if ($count >= $limit) {
            return $limit;
        }

        return null;
    }

    public function clearCache(string $planType): void
    {
        Cache::forget($this->cacheKey($planType));
    }

    private function cacheKey(string $planType): string
    {
        return "plan_limits:{$planType}";
    }
}
