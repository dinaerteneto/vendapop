<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Services\TenantService;

trait BelongsToTenant
{
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (!$model->tenant_id
                && !array_key_exists('tenant_id', $model->getAttributes())
                && $tenantId = app(TenantService::class)->getTenantId()) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

