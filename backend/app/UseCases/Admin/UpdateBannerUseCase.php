<?php

namespace App\UseCases\Admin;

use App\Models\RotatingBanner;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;
use App\Services\BannerService;
use Illuminate\Http\UploadedFile;

class UpdateBannerUseCase
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
        RotatingBanner $banner,
        ?UploadedFile $imageFile = null,
        ?string $imageUrl = null,
        ?string $linkUrl = null,
        ?string $title = null,
        ?string $description = null,
        ?int $order = null,
        ?bool $isActive = null
    ) {
        $updateData = [];

        // Handle image update
        if ($imageFile || $imageUrl !== null) {
            $imageData = $this->bannerService->updateImage($banner, $imageFile, $imageUrl);
            if (!empty($imageData)) {
                $updateData = array_merge($updateData, $imageData);
            }
        }

        // Handle other fields
        if ($linkUrl !== null) {
            $updateData['link_url'] = $linkUrl;
        }
        if ($title !== null) {
            $updateData['title'] = $title;
        }
        if ($description !== null) {
            $updateData['description'] = $description;
        }
        if ($order !== null) {
            $updateData['order'] = $order;
        }
        if ($isActive !== null) {
            $updateData['is_active'] = $isActive;
        }

        if (!empty($updateData)) {
            $this->bannerRepository->update($banner, $updateData);
        }

        return $banner->fresh();
    }
}

