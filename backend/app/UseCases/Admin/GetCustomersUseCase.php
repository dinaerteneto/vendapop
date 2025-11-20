<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCustomersUseCase
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(Tenant $tenant, int $perPage = 20, ?string $sortBy = 'id', ?string $sortDirection = 'desc'): LengthAwarePaginator
    {
        return $this->customerRepository->findByTenantWithPagination(
            $tenant->id,
            $perPage,
            $sortBy,
            $sortDirection
        );
    }
}

