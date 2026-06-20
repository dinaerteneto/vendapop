<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialEmailsSent extends Model
{
    protected $table = 'trial_emails_sent';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'email_day',
        'sent_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
