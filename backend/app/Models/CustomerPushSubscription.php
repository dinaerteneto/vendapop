<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_uuid',
        'endpoint',
        'public_key',
        'auth_token',
    ];
}
