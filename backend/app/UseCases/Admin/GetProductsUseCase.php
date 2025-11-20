<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetProductsUseCase
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(Tenant $tenant, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        return $this->productRepository->findByTenantWithPagination(
            $tenant->id,
            $perPage,
            $sortBy,
            $sortDirection
        );
    }
}

