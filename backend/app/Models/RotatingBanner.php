<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RotatingBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'image_url',
        'image_path',
        'is_external',
        'link_url',
        'order',
        'is_active',
        'title',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_external' => 'boolean',
        'order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
