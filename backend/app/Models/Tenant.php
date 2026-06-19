<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'whatsapp_number',
        'whatsapp_message',
        'logo_url',
        'logo_path',
        'logo_is_external',
        'primary_color',
        'secondary_color',
        'description',
        'banner_message',
        'banner_text_color_1',
        'banner_text_color_2',
        'banner_background_color',
        'address',
        'email_contact',
        'business_sector',
        'plan_expiry_banner_dismissed_at',
        'onboarding_completed',
        'onboarding_step',
    ];

    protected $casts = [
        'logo_is_external' => 'boolean',
        'onboarding_completed' => 'boolean',
        'onboarding_step' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function socials()
    {
        return $this->hasMany(TenantSocial::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function rotatingBanners()
    {
        return $this->hasMany(RotatingBanner::class)->orderBy('order');
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('order');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function trackings()
    {
        return $this->hasMany(TenantTracking::class);
    }

    public function getLogoUrlAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        return preg_replace('/^http:\/\//i', 'https://', $value);
    }
}
