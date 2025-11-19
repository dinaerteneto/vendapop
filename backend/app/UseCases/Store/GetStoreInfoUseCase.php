<?php

namespace App\UseCases\Store;

use App\Models\Tenant;
use App\Repositories\Interfaces\TenantRepositoryInterface;

class GetStoreInfoUseCase
{
    private TenantRepositoryInterface $tenantRepository;

    public function __construct(TenantRepositoryInterface $tenantRepository)
    {
        $this->tenantRepository = $tenantRepository;
    }

    public function execute(string $storeSlug): ?Tenant
    {
        $tenant = $this->tenantRepository->findBySlug($storeSlug);

        if ($tenant) {
            $tenant->load('socials');
        }

        return $tenant;
    }
}
