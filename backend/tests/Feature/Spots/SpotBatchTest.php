<?php

namespace Tests\Feature\Spots;

use App\Models\SpotBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SpotBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_returns_total_spots_minus_used_spots(): void
    {
        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 30,
        ]);

        $this->assertEquals(70, $batch->remaining());
    }

    public function test_remaining_returns_zero_when_used_spots_exceeds_or_equals_total(): void
    {
        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 100,
        ]);

        $this->assertEquals(0, $batch->remaining());

        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 150,
        ]);

        $this->assertEquals(0, $batch->remaining());
    }

    public function test_isFull_returns_true_when_used_spots_exceeds_or_equals_total(): void
    {
        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 100,
        ]);

        $this->assertTrue($batch->isFull());

        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 150,
        ]);

        $this->assertTrue($batch->isFull());
    }

    public function test_isFull_returns_false_when_used_spots_is_less_than_total(): void
    {
        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 50,
        ]);

        $this->assertFalse($batch->isFull());
    }

    public function test_migration_creates_table_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('spot_batches'));
        $this->assertTrue(Schema::hasColumns('spot_batches', [
            'id', 'total_spots', 'used_spots', 'batch_label', 'replenishes_at', 'created_at', 'updated_at',
        ]));
    }

    public function test_model_cast_types(): void
    {
        $batch = SpotBatch::create([
            'total_spots' => 100,
            'used_spots' => 0,
        ]);

        $this->assertIsInt($batch->total_spots);
        $this->assertIsInt($batch->used_spots);
    }

    public function test_initial_batch_exists_after_seed(): void
    {
        $this->seed(\Database\Seeders\SpotBatchesSeeder::class);

        $this->assertDatabaseHas('spot_batches', [
            'total_spots' => 100,
            'used_spots' => 0,
        ]);
    }
}
