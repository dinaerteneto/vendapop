<?php

namespace App\UseCases\SuperAdmin;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetTenantsUseCase
{
    public function execute(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 20,
        ?string $planType = null,
        ?string $planStatus = null
    ): LengthAwarePaginator {
        return Tenant::query()
            ->with(['subscriptions', 'users'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when($planType || $planStatus, fn ($q) => $q->whereHas('subscriptions',
                fn ($sq) => $sq
                    ->when($planType, fn ($sq2) => $sq2->where('plan_type', $planType))
                    ->when($planStatus, fn ($sq2) => $sq2->where('plan_status', $planStatus))
            ))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }
}
