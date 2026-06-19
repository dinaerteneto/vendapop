<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpotBatch extends Model
{
    protected $fillable = [
        'total_spots',
        'used_spots',
        'batch_label',
        'replenishes_at',
    ];

    protected $casts = [
        'total_spots' => 'integer',
        'used_spots' => 'integer',
    ];

    public function remaining(): int
    {
        return max(0, $this->total_spots - $this->used_spots);
    }

    public function isFull(): bool
    {
        return $this->used_spots >= $this->total_spots;
    }
}
