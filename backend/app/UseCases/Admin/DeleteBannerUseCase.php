<?php

namespace App\UseCases\Admin;

use App\Models\RotatingBanner;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;
use App\Services\BannerService;

class DeleteBannerUseCase
{
    private RotatingBannerRepositoryInterface $bannerRepository;
    private BannerService $bannerService;

    public function __construct(
        RotatingBannerRepositoryInterface $bannerRepository,
        BannerService $bannerService
    ) {
        $this->bannerRepository = $bannerRepository;
        $this->bannerService = $bannerService;
    }

    public function execute(RotatingBanner $banner): void
    {
        // Delete image file if it's a local upload
        $this->bannerService->deleteImageIfLocal($banner);

        // Delete banner
        $this->bannerRepository->delete($banner);
    }
}

