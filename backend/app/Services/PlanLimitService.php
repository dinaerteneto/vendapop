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

    public function countActiveCategories(Tenant $tenant): int
    {
        return $tenant->categories()->where('is_active', true)->count();
    }

    public function countOrdersThisMonth(Tenant $tenant): int
    {
        return $tenant->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function resolvePlanType(Tenant $tenant): string
    {
        $subscription = $tenant->subscriptions()
            ->whereIn('plan_status', ['active', 'trial'])
            ->latest()
            ->first();

        return $subscription?->plan_type ?? 'free';
    }

    public function checkLimit(Tenant $tenant, string $resourceType = 'products'): ?int
    {
        $planType = $this->resolvePlanType($tenant);

        $planLimit = $this->getLimits($planType);

        if (!$planLimit) {
            return null;
        }

        $column = $this->resourceTypeToColumn($resourceType);
        $limit = $planLimit->{$column};

        if ($limit === null || $limit === 0) {
            return null;
        }

        $count = match ($resourceType) {
            'categories' => $this->countActiveCategories($tenant),
            'orders' => $this->countOrdersThisMonth($tenant),
            default => $this->countActiveProducts($tenant),
        };

        if ($count >= $limit) {
            return $limit;
        }

        return null;
    }

    private function resourceTypeToColumn(string $resourceType): string
    {
        return match ($resourceType) {
            'products' => 'max_products',
            'categories' => 'max_categories',
            'orders' => 'max_orders_per_month',
            default => 'max_products',
        };
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
