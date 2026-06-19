<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_type',
        'plan_status',
        'invite_id',
        'invite_source',
        'started_at',
        'ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invite()
    {
        return $this->belongsTo(Invite::class);
    }

    public function trialEmailsSent()
    {
        return $this->hasMany(TrialEmailsSent::class);
    }

    public function isTrial(): bool
    {
        return $this->plan_status === 'trial';
    }

    public function isActive(): bool
    {
        return in_array($this->plan_status, ['active', 'trial']);
    }

    public function isExpired(): bool
    {
        if ($this->ends_at === null) {
            return false;
        }

        return $this->ends_at->isPast();
    }

    public function daysRemaining(): ?int
    {
        if ($this->ends_at === null) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }
}
