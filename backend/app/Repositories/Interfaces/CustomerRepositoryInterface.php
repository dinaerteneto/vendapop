<?php

namespace App\Repositories\Interfaces;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;
    public function findByIdAndTenant(int $id, int $tenantId): ?Customer;
    public function findByTenant(int $tenantId): Collection;
    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator;
    public function create(array $data): Customer;
    public function update(Customer $customer, array $data): bool;
}

