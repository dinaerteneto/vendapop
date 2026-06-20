<?php

namespace Tests\Feature\Spots;

use App\Models\SpotBatch;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpotsRemainingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SpotBatch::query()->delete();
    }

    public function test_endpoint_returns_200(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        $response = $this->getJson('/api/spots/remaining');

        $response->assertStatus(200);
    }

    public function test_endpoint_returns_correct_structure(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 3]);

        $response = $this->getJson('/api/spots/remaining');

        $response->assertJsonStructure([
            'remaining',
            'total',
            'next_replenish',
        ]);
    }

    public function test_endpoint_returns_correct_values(): void
    {
        SpotBatch::create(['total_spots' => 30, 'used_spots' => 10]);
        SpotBatch::create(['total_spots' => 20, 'used_spots' => 5]);

        $response = $this->getJson('/api/spots/remaining');

        $response->assertJson([
            'remaining' => 35,
            'total' => 50,
        ]);
    }

    public function test_endpoint_works_without_authentication(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        $response = $this->getJson('/api/spots/remaining');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('next_replenish'));
    }

    public function test_endpoint_returns_zero_when_all_spots_consumed(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 10]);

        $response = $this->getJson('/api/spots/remaining');

        $response->assertJson([
            'remaining' => 0,
            'total' => 10,
        ]);
    }

    public function test_endpoint_returns_zero_when_no_batches(): void
    {
        $response = $this->getJson('/api/spots/remaining');

        $response->assertJson([
            'remaining' => 0,
            'total' => 0,
        ]);
    }

    public function test_next_replenish_is_valid_iso8601(): void
    {
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        $response = $this->getJson('/api/spots/remaining');
        $nextReplenish = $response->json('next_replenish');

        $this->assertIsString($nextReplenish);
        $this->assertNotEmpty($nextReplenish);
        $this->assertTrue(
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $nextReplenish) === 1,
            "next_replenish must be ISO8601, got: $nextReplenish",
        );
    }

    public function test_endpoint_response_matches_spot_service(): void
    {
        SpotBatch::create(['total_spots' => 15, 'used_spots' => 0]);

        $response = $this->getJson('/api/spots/remaining');
        $service = $this->app->make(\App\Contracts\SpotServiceInterface::class);

        $response->assertJson([
            'remaining' => $service->remaining(),
        ]);
    }
}
