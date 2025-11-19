<?php

namespace App\Repositories\Interfaces;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;
    public function findBySlug(string $slug): ?Tenant;
    public function findAll(): Collection;
    public function create(array $data): Tenant;
    public function update(Tenant $tenant, array $data): bool;
}
