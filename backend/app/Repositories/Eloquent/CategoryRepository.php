<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function findByIdAndTenant(int $id, int $tenantId): ?Category
    {
        return Category::where('id', $id)
                      ->where('tenant_id', $tenantId)
                      ->first();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return Category::where('tenant_id', $tenantId)
                      ->where('is_active', true)
                      ->orderBy('name')
                      ->get();
    }

    public function findByTenantWithPagination(int $tenantId, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        $query = Category::where('tenant_id', $tenantId);

        $allowedSorts = ['id', 'name', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
