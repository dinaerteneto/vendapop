<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findByIdAndTenant(int $id, int $tenantId): ?Order;
    public function findByUuid(string $uuid): ?Order;
    public function findByUuidAndTenant(string $uuid, int $tenantId): ?Order;
    public function findByTenant(int $tenantId): Collection;
    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc', ?string $status = null): LengthAwarePaginator;
    public function findByTenantAndStatus(int $tenantId, string $status): Collection;
    public function create(array $data): Order;
    public function update(Order $order, array $data): bool;
    public function generateOrderNumber(int $tenantId): string;
}
