<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, BelongsToTenant, HasSlug;

    protected $fillable = ['name', 'slug', 'uuid', 'image_url', 'is_active', 'tenant_id'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
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

