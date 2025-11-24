<?php

namespace App\Repositories\Interfaces;

use App\Models\RotatingBanner;
use Illuminate\Database\Eloquent\Collection;

interface RotatingBannerRepositoryInterface
{
    public function findById(int $id): ?RotatingBanner;
    public function findByIdAndTenant(int $id, int $tenantId): ?RotatingBanner;
    public function findByTenant(int $tenantId): Collection;
    public function findActiveByTenant(int $tenantId): Collection;
    public function getMaxOrderByTenant(int $tenantId): int;
    public function create(array $data): RotatingBanner;
    public function update(RotatingBanner $banner, array $data): bool;
    public function delete(RotatingBanner $banner): bool;
    public function updateOrder(int $tenantId, array $bannersData): void;
}

