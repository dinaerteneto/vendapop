<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $fillable = [
        'code',
        'type',
        'created_by_tenant_id',
        'max_uses',
        'current_uses',
        'expires_at',
    ];

    protected $casts = [
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Tenant::class, 'created_by_tenant_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->current_uses >= $this->max_uses;
    }

    public function slotsRemaining(): int
    {
        return max(0, $this->max_uses - $this->current_uses);
    }
}
