<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'notes', 'uuid'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the route key for the model (use UUID instead of ID)
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

