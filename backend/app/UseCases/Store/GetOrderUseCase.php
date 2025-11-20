<?php

namespace App\UseCases\Store;

use App\Models\Order;
use App\Models\Tenant;

class GetOrderUseCase
{
    public function execute(Tenant $tenant, string $uuid): ?Order
    {
        return Order::with(['items.product', 'customer'])
            ->where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}

