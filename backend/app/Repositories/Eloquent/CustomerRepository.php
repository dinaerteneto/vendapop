<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function findByIdAndTenant(int $id, int $tenantId): ?Customer
    {
        return Customer::where('id', $id)
                     ->where('tenant_id', $tenantId)
                     ->first();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Customer::where('tenant_id', $tenantId)
                     ->withCount('orders')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        $query = Customer::where('tenant_id', $tenantId)
                     ->withCount('orders');

        $allowedSorts = ['id', 'name', 'email', 'phone', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }
}

