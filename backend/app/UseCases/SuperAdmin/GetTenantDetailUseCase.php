<?php

namespace App\UseCases\SuperAdmin;

use App\Models\Tenant;

class GetTenantDetailUseCase
{
    public function execute(int $tenantId): ?Tenant
    {
        return Tenant::with([
            'subscriptions' => fn ($q) => $q->orderBy('created_at', 'desc'),
            'users',
        ])
            ->withCount(['products', 'orders'])
            ->findOrFail($tenantId);
    }
}
