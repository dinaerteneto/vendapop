<?php

namespace App\UseCases\Store;

use App\Models\Tenant;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetProductsUseCase
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(Tenant $tenant, ?string $search = null, ?int $categoryId = null): Collection
    {
        if ($search) {
            return $this->productRepository->searchByTenant($tenant->id, $search);
        }

        if ($categoryId) {
            return $this->productRepository->filterByCategory($tenant->id, $categoryId);
        }

        return $this->productRepository->findActiveByTenant($tenant->id);
    }
}
