<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetBannersUseCase
{
    private RotatingBannerRepositoryInterface $bannerRepository;

    public function __construct(RotatingBannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function execute(Tenant $tenant): Collection
    {
        return $this->bannerRepository->findByTenant($tenant->id);
    }
}

