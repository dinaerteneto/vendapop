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
        'store_url',
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
    ];

    protected $casts = [
        'logo_is_external' => 'boolean',
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
}
