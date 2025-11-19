<?php

namespace App\UseCases\Store;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class GetProductDetailsUseCase
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(int $tenantId, int $productId): ?Product
    {
        return $this->productRepository->findByIdAndTenant($productId, $tenantId);
    }
}
