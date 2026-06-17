<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaitlistEntry extends Model
{
    protected $fillable = ['email', 'status', 'rejection_reason', 'invite_id'];
}
