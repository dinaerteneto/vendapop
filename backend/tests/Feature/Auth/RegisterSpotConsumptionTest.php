<?php

namespace Tests\Feature\Auth;

use App\Models\Invite;
use App\Models\SpotBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterSpotConsumptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SpotBatch::query()->delete();
    }

    private function validPayload(): array
    {
        return [
            'store_name' => 'Test Store',
            'store_slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
            'email' => uniqid() . '@example.com',
            'terms_accepted' => true,
        ];
    }

    public function test_registration_without_invite_consumes_spot_when_available(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        $response = $this->postJson('/api/admin/register', $this->validPayload());

        $response->assertStatus(201);

        $batch = SpotBatch::first();
        $this->assertEquals(1, $batch->used_spots);
    }

    public function test_registration_without_invite_returns_422_when_spots_exhausted(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 1]);

        $response = $this->postJson('/api/admin/register', $this->validPayload());

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Vagas esgotadas no momento.',
                'redirect_to' => 'waitlist',
            ]);
    }

    public function test_registration_without_invite_does_not_create_account_when_spots_exhausted(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 1]);

        $payload = $this->validPayload();

        $this->postJson('/api/admin/register', $payload);

        $this->assertDatabaseMissing('tenants', ['slug' => $payload['store_slug']]);
    }

    public function test_valid_invite_bypasses_spot_check_when_spots_exhausted(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 1]);

        Invite::create([
            'code' => 'BYPS001',
            'type' => 'manual',
            'created_by_tenant_id' => null,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
            'is_active' => true,
        ]);

        $payload = $this->validPayload();
        $payload['invite_code'] = 'BYPS001';

        $response = $this->postJson('/api/admin/register', $payload);

        $response->assertStatus(201);

        $batch = SpotBatch::first();
        $this->assertEquals(1, $batch->used_spots);
    }

    public function test_valid_invite_does_not_consume_spot_when_spots_available(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        Invite::create([
            'code' => 'BYPS002',
            'type' => 'manual',
            'created_by_tenant_id' => null,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
            'is_active' => true,
        ]);

        $payload = $this->validPayload();
        $payload['invite_code'] = 'BYPS002';

        $response = $this->postJson('/api/admin/register', $payload);

        $response->assertStatus(201);

        $batch = SpotBatch::first();
        $this->assertEquals(0, $batch->used_spots);
    }

    public function test_invalid_invite_returns_validation_error_and_does_not_consume_spot(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 10, 'used_spots' => 0]);

        $payload = $this->validPayload();
        $payload['invite_code'] = 'INVALID';

        $response = $this->postJson('/api/admin/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['invite_code']);

        $batch = SpotBatch::first();
        $this->assertEquals(0, $batch->used_spots);
    }

    public function test_invalid_invite_returns_validation_error_when_spots_exhausted(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 1]);

        $payload = $this->validPayload();
        $payload['invite_code'] = 'INVALID';

        $response = $this->postJson('/api/admin/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['invite_code']);
    }

    public function test_spot_consumption_rolls_back_when_registration_fails(): void
    {
        Mail::fake();
        SpotBatch::create(['total_spots' => 1, 'used_spots' => 0]);

        $this->mock(\App\Services\DemoDataService::class, function ($mock) {
            $mock->shouldReceive('seedFor')->andThrow(new \Exception('fail'));
        });

        $payload = $this->validPayload();
        $this->postJson('/api/admin/register', $payload);

        $batch = SpotBatch::first();
        $this->assertEquals(0, $batch->used_spots);
    }
}
