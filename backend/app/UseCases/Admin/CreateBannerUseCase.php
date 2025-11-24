<?php

namespace App\UseCases\Admin;

use App\Models\Tenant;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;
use App\Services\BannerService;
use Illuminate\Http\UploadedFile;

class CreateBannerUseCase
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

    public function execute(
        Tenant $tenant,
        ?UploadedFile $imageFile = null,
        ?string $imageUrl = null,
        ?string $linkUrl = null,
        ?string $title = null,
        ?string $description = null,
        ?int $order = null,
        bool $isActive = true
    ) {
        // Process image (upload or URL)
        $imageData = $this->bannerService->processImage($imageFile, $imageUrl);

        // Get order if not provided
        if ($order === null) {
            $order = $this->bannerRepository->getMaxOrderByTenant($tenant->id) + 1;
        }

        $bannerData = array_merge([
            'tenant_id' => $tenant->id,
            'link_url' => $linkUrl,
            'title' => $title,
            'description' => $description,
            'order' => $order,
            'is_active' => $isActive,
        ], $imageData);

        return $this->bannerRepository->create($bannerData);
    }
}

