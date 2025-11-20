<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findByIdAndTenant(int $id, int $tenantId): ?Product
    {
        return Product::where('id', $id)
                     ->where('tenant_id', $tenantId)
                     ->first();
    }

    public function findActiveByTenant(int $tenantId): Collection
    {
        return Product::where('tenant_id', $tenantId)
                     ->where('is_active', true)
                     ->with('category')
                     ->get();
    }

    public function findActiveByTenantWithPagination(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::where('tenant_id', $tenantId)
                     ->where('is_active', true)
                     ->with('category')
                     ->paginate($perPage);
    }

    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        $query = Product::where('tenant_id', $tenantId)
                     ->with(['category', 'images']);

        // Ordenação especial para categoria
        if ($sortBy === 'category') {
            $query->join('categories', 'products.category_id', '=', 'categories.id')
                  ->select('products.*')
                  ->orderBy('categories.name', $sortDirection)
                  ->groupBy('products.id');
        } else {
            $allowedSorts = ['id', 'name', 'price', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        return $query->paginate($perPage);
    }

    public function searchByTenant(int $tenantId, string $searchTerm): Collection
    {
        return Product::where('tenant_id', $tenantId)
                     ->where('is_active', true)
                     ->where('name', 'like', "%{$searchTerm}%")
                     ->with('category')
                     ->get();
    }

    public function filterByCategory(int $tenantId, int $categoryId): Collection
    {
        return Product::where('tenant_id', $tenantId)
                     ->where('is_active', true)
                     ->where('category_id', $categoryId)
                     ->with('category')
                     ->get();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
