<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetOrdersUseCase
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function execute(Tenant $tenant, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc', ?string $status = null): LengthAwarePaginator
    {
        return $this->orderRepository->findByTenantWithPagination(
            $tenant->id,
            $perPage,
            $sortBy,
            $sortDirection,
            $status
        );
    }
}

