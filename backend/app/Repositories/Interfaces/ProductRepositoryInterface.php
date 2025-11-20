<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findByIdAndTenant(int $id, int $tenantId): ?Product;
    public function findActiveByTenant(int $tenantId): Collection;
    public function findActiveByTenantWithPagination(int $tenantId, int $perPage = 15): LengthAwarePaginator;
    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator;
    public function searchByTenant(int $tenantId, string $searchTerm): Collection;
    public function filterByCategory(int $tenantId, int $categoryId): Collection;
    public function create(array $data): Product;
    public function update(Product $product, array $data): bool;
    public function delete(Product $product): bool;
}
