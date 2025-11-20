<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByIdAndTenant(int $id, int $tenantId): ?Order
    {
        return Order::where('id', $id)
                   ->where('tenant_id', $tenantId)
                   ->first();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Order::where('tenant_id', $tenantId)
                   ->with(['customer', 'items.product'])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc', ?string $status = null): LengthAwarePaginator
    {
        $query = Order::where('tenant_id', $tenantId)
                   ->with(['customer', 'items.product']);

        if ($status) {
            $query->where('status', $status);
        }

        $allowedSorts = ['id', 'order_number', 'status', 'total_amount', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function findByTenantAndStatus(int $tenantId, string $status): Collection
    {
        return Order::where('tenant_id', $tenantId)
                   ->where('status', $status)
                   ->with(['customer', 'items.product'])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function generateOrderNumber(int $tenantId): string
    {
        $count = Order::where('tenant_id', $tenantId)->count() + 1;
        return sprintf('PED-%s-%06d', date('Y'), $count);
    }
}
