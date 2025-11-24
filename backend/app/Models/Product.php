<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasFactory, BelongsToTenant, HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'uuid',
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
        'tenant_id',
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

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

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(255)
            ->usingSeparator('-')
            ->allowDuplicateSlugs(); // Unique constraint no banco garante unicidade por tenant
    }

    /**
     * Get the route key for the model
     * Admin routes use UUID, public routes use slug
     */
    public function getRouteKeyName(): string
    {
        // Check if current path is admin route
        $path = request()->path();
        if (str_starts_with($path, 'api/admin')) {
            return 'uuid';
        }
        return 'slug';
    }
}
