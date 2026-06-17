<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantTracking extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'tracking_code',
    ];
}
