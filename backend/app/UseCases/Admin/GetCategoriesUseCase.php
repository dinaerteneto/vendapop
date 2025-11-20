<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCategoriesUseCase
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function execute(Tenant $tenant, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        return $this->categoryRepository->findByTenantWithPagination(
            $tenant->id,
            $perPage,
            $sortBy,
            $sortDirection
        );
    }
}

