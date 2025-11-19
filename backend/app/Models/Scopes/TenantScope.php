<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Services\TenantService;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if ($tenantId = app(TenantService::class)->getTenantId()) {
            $builder->where('tenant_id', $tenantId);
        }
    }
}

