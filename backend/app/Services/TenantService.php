<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService
{
    protected ?Tenant $tenant = null;

    public function setTenant(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function clearTenant(): void
    {
        $this->tenant = null;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function getTenantId(): ?int
    {
        return $this->tenant ? $this->tenant->id : null;
    }
}

