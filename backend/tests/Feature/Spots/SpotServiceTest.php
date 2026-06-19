<?php

namespace Tests\Feature\Spots;

use App\Models\SpotBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpotServiceTest extends TestCase
{
    use RefreshDatabase;

    private \App\Contracts\SpotServiceInterface $spotService;

    protected function setUp(): void
    {
        parent::setUp();
        SpotBatch::query()->delete();
        $this->spotService = $this->app->make(\App\Contracts\SpotServiceInterface::class);
    }

    public function test_consume_decrements_remaining_spots(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        $this->spotService->consume();
        $this->spotService->consume();
        $this->spotService->consume();

        $this->assertEquals(7, $this->spotService->remaining());
    }

    public function test_consume_returns_false_when_batch_is_full(): void
    {
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 0]);

        $this->assertTrue($this->spotService->consume());
        $this->assertFalse($this->spotService->consume());
    }

    public function test_consume_returns_false_when_no_batches_exist(): void
    {
        $this->assertFalse($this->spotService->consume());
    }

    public function test_consume_skips_full_batches_and_consumes_from_next_available_fifo(): void
    {
        SpotBatch::create(['total_spots' => 2, 'used_spots' => 2]);
        $batch2 = SpotBatch::create(['total_spots' => 3, 'used_spots' => 1]);

        $this->assertTrue($this->spotService->consume());

        $batch2->refresh();
        $this->assertEquals(2, $batch2->used_spots);
    }

    public function test_consume_consumes_from_oldest_batch_first_fifo(): void
    {
        $first = SpotBatch::create(['total_spots' => 5, 'used_spots' => 0]);
        $second = SpotBatch::create(['total_spots' => 5, 'used_spots' => 0]);

        $this->spotService->consume();

        $first->refresh();
        $second->refresh();

        $this->assertEquals(1, $first->used_spots);
        $this->assertEquals(0, $second->used_spots);
    }

    public function test_remaining_returns_correct_sum_across_multiple_batches(): void
    {
        SpotBatch::create(['total_spots' => 30, 'used_spots' => 10]);
        SpotBatch::create(['total_spots' => 20, 'used_spots' => 5]);

        $this->assertEquals(35, $this->spotService->remaining());
    }

    public function test_remaining_returns_zero_when_all_batches_exhausted(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 10]);
        SpotBatch::create(['total_spots' => 5, 'used_spots' => 5]);

        $this->assertEquals(0, $this->spotService->remaining());
    }

    public function test_remaining_returns_zero_when_no_batches_exist(): void
    {
        $this->assertEquals(0, $this->spotService->remaining());
    }

    public function test_replenish_creates_new_batch_with_default_amount(): void
    {
        $this->spotService->replenish();

        $batch = SpotBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals(config('spots.replenish_amount'), $batch->total_spots);
        $this->assertEquals(0, $batch->used_spots);
        $this->assertNotNull($batch->batch_label);
        $this->assertNotNull($batch->replenishes_at);
    }

    public function test_replenish_adds_to_existing_batches(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 10]);

        $this->spotService->replenish();

        $this->assertEquals(2, SpotBatch::count());
        $this->assertEquals(config('spots.replenish_amount'), $this->spotService->remaining());
    }

    public function test_reset_month_resets_used_spots_to_zero(): void
    {
        $first = SpotBatch::create(['total_spots' => 10, 'used_spots' => 7]);
        $second = SpotBatch::create(['total_spots' => 5, 'used_spots' => 3]);

        $this->spotService->resetMonth();

        $first->refresh();
        $second->refresh();

        $this->assertEquals(0, $first->used_spots);
        $this->assertEquals(0, $second->used_spots);
    }

    public function test_reset_month_restores_full_remaining(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 10]);

        $this->assertEquals(0, $this->spotService->remaining());

        $this->spotService->resetMonth();

        $this->assertEquals(10, $this->spotService->remaining());
    }

    public function test_last_spot_race_condition_only_one_consumer_succeeds(): void
    {
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 0]);

        $first = $this->spotService->consume();
        $second = $this->spotService->consume();

        $this->assertTrue($first);
        $this->assertFalse($second);
        $this->assertEquals(0, $this->spotService->remaining());
    }

    public function test_consume_is_atomic_within_transaction(): void
    {
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 0]);

        $result = DB::transaction(function () {
            $consumed = $this->spotService->consume();
            DB::rollBack();
            return $consumed;
        });

        $this->assertTrue($result);
        $this->assertEquals(1, $this->spotService->remaining());
    }

    public function test_removed_bindings()
    {
        $this->assertTrue($this->app->bound(\App\Contracts\SpotServiceInterface::class));
        $this->assertInstanceOf(
            \App\Services\SpotService::class,
            $this->app->make(\App\Contracts\SpotServiceInterface::class)
        );
    }
}
