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
        'logo_url',
        'primary_color',
        'secondary_color',
        'description',
        'address',
        'email_contact',
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
}
