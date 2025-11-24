<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;

class UpdateBannerOrderUseCase
{
    private RotatingBannerRepositoryInterface $bannerRepository;

    public function __construct(RotatingBannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function execute(Tenant $tenant, array $bannersData): void
    {
        $this->bannerRepository->updateOrder($tenant->id, $bannersData);
    }
}

