<?php

namespace App\Repositories\Eloquent;

use App\Models\RotatingBanner;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RotatingBannerRepository implements RotatingBannerRepositoryInterface
{
    public function findById(int $id): ?RotatingBanner
    {
        return RotatingBanner::find($id);
    }

    public function findByIdAndTenant(int $id, int $tenantId): ?RotatingBanner
    {
        return RotatingBanner::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return RotatingBanner::where('tenant_id', $tenantId)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();
    }

    public function findActiveByTenant(int $tenantId): Collection
    {
        return RotatingBanner::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();
    }

    public function getMaxOrderByTenant(int $tenantId): int
    {
        $maxOrder = RotatingBanner::where('tenant_id', $tenantId)->max('order');
        return $maxOrder ? (int) $maxOrder : 0;
    }

    public function create(array $data): RotatingBanner
    {
        return RotatingBanner::create($data);
    }

    public function update(RotatingBanner $banner, array $data): bool
    {
        return $banner->update($data);
    }

    public function delete(RotatingBanner $banner): bool
    {
        return $banner->delete();
    }

    public function updateOrder(int $tenantId, array $bannersData): void
    {
        foreach ($bannersData as $bannerData) {
            RotatingBanner::where('tenant_id', $tenantId)
                ->where('id', $bannerData['id'])
                ->update(['order' => $bannerData['order']]);
        }
    }
}

