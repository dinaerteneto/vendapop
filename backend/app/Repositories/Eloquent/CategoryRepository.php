<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
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
