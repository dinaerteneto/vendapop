<?php

namespace App\Repositories\Eloquent;

use App\Models\Tenant;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TenantRepository implements TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    public function findAll(): Collection
    {
        return Tenant::all();
    }

    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function update(Tenant $tenant, array $data): bool
    {
        return $tenant->update($data);
    }
}
