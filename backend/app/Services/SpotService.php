<?php

namespace App\Services;

use App\Contracts\SpotServiceInterface;
use App\Models\SpotBatch;
use Illuminate\Support\Facades\DB;

class SpotService implements SpotServiceInterface
{
    public function consume(): bool
    {
        return DB::transaction(function () {
            $batch = SpotBatch::whereColumn('used_spots', '<', 'total_spots')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$batch) {
                return false;
            }

            $batch->increment('used_spots');

            return true;
        });
    }

    public function remaining(): int
    {
        return (int) SpotBatch::sum(DB::raw('COALESCE(total_spots, 0) - COALESCE(used_spots, 0)'));
    }

    public function replenish(): void
    {
        SpotBatch::create([
            'total_spots' => config('spots.replenish_amount'),
            'used_spots' => 0,
            'batch_label' => 'weekly-' . now()->format('Y-m-d'),
            'replenishes_at' => now(),
        ]);
    }

    public function resetMonth(): void
    {
        SpotBatch::query()->update(['used_spots' => 0]);
    }
}
