<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    /**
     * Process image upload or URL
     * 
     * @param UploadedFile|null $imageFile
     * @param string|null $imageUrl
     * @return array ['image_url', 'image_path', 'is_external']
     */
    public function processImage(?UploadedFile $imageFile = null, ?string $imageUrl = null): array
    {
        if ($imageFile) {
            // Upload local
            $imagePath = $imageFile->store('banners', 'public');
            return [
                'image_url' => url(Storage::url($imagePath)),
                'image_path' => $imagePath,
                'is_external' => false,
            ];
        }

        if ($imageUrl) {
            // URL externa
            return [
                'image_url' => $imageUrl,
                'image_path' => null,
                'is_external' => true,
            ];
        }

        throw new \InvalidArgumentException('É necessário fornecer uma imagem (upload ou URL)');
    }

    /**
     * Delete image file if it's a local upload
     * 
     * @param RotatingBanner $banner
     * @return void
     */
    public function deleteImageIfLocal(\App\Models\RotatingBanner $banner): void
    {
        if (!$banner->is_external && $banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
    }

    /**
     * Update image handling both upload and URL
     * 
     * @param RotatingBanner $banner
     * @param UploadedFile|null $imageFile
     * @param string|null $imageUrl
     * @return array ['image_url', 'image_path', 'is_external']
     */
    public function updateImage(\App\Models\RotatingBanner $banner, ?UploadedFile $imageFile = null, ?string $imageUrl = null): array
    {
        if ($imageFile) {
            // Delete old image if it was local
            $this->deleteImageIfLocal($banner);

            // Upload new image
            return $this->processImage($imageFile);
        }

        if ($imageUrl !== null) {
            // URL foi fornecida
            if ($banner->image_url !== $imageUrl) {
                // URL mudou, delete old image if local
                $this->deleteImageIfLocal($banner);

                return [
                    'image_url' => $imageUrl,
                    'image_path' => null,
                    'is_external' => true,
                ];
            }
        }

        // No changes to image
        return [];
    }
}

