<?php

namespace App\UseCases\Store;

use App\Models\Tenant;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetCategoriesUseCase
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function execute(Tenant $tenant): Collection
    {
        return $this->categoryRepository->findByTenant($tenant->id);
    }
}
