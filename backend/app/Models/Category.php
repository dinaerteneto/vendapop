<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['name', 'slug', 'image_url', 'is_active', 'tenant_id'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

