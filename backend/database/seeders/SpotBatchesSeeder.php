<?php

namespace Database\Seeders;

use App\Models\SpotBatch;
use Illuminate\Database\Seeder;

class SpotBatchesSeeder extends Seeder
{
    public function run(): void
    {
        SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 0,
        ]);
    }
}
