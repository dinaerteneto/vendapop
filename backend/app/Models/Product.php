<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'promotional_price',
        'sizes',
        'colors',
        // 'main_image_url', // Removed
        // 'images', // Removed
        'is_active',
        'is_hot',
    ];

    protected $casts = [
        'sizes' => 'array',
        'colors' => 'array',
        // 'images' => 'array', // Removed
        'is_active' => 'boolean',
        'is_hot' => 'boolean',
        'price' => 'decimal:2',
        'promotional_price' => 'decimal:2',
    ];

    protected $with = ['images']; // Eager load by default usually helpful for products

    protected $appends = ['main_image_url']; // Virtual attribute

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getMainImageUrlAttribute()
    {
        // Search in the loaded collection to avoid extra queries
        $main = $this->images->firstWhere('is_main', true);

        // Fallback to the first image if no main is set
        if (!$main) {
            $main = $this->images->first();
        }

        return $main ? $main->url : null;
    }
}
