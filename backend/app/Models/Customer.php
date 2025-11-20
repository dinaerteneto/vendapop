<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'notes'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

