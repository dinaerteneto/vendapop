<?php

namespace Tests\Feature;

use App\Models\SpotBatch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminSpotTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        Sanctum::actingAs($this->superAdmin);
    }

    public function test_lists_all_batches(): void
    {
        SpotBatch::create(['total_spots' => 100, 'used_spots' => 30]);
        SpotBatch::create(['total_spots' => 50, 'used_spots' => 10]);

        $response = $this->getJson('/api/superadmin/spots');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_index_includes_remaining(): void
    {
        SpotBatch::create(['total_spots' => 100, 'used_spots' => 30]);

        $response = $this->getJson('/api/superadmin/spots');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['total_spots', 'used_spots', 'remaining'],
            ],
        ]);
    }

    public function test_creates_new_batch(): void
    {
        $response = $this->postJson('/api/superadmin/spots', [
            'total_spots' => 100,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(100, $response->json('total_spots'));
        $this->assertEquals(0, $response->json('used_spots'));
    }

    public function test_rejects_zero_total_spots(): void
    {
        $response = $this->postJson('/api/superadmin/spots', [
            'total_spots' => 0,
        ]);

        $response->assertStatus(422);
    }

    public function test_rejects_negative_total_spots(): void
    {
        $response = $this->postJson('/api/superadmin/spots', [
            'total_spots' => -5,
        ]);

        $response->assertStatus(422);
    }

    public function test_accepts_batch_label(): void
    {
        $response = $this->postJson('/api/superadmin/spots', [
            'total_spots' => 50,
            'batch_label' => 'Parceiros Evento X',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('Parceiros Evento X', $response->json('batch_label'));
    }

    public function test_replenish_resets_used_spots(): void
    {
        $batch = SpotBatch::create(['total_spots' => 100, 'used_spots' => 80]);

        $response = $this->putJson("/api/superadmin/spots/{$batch->id}/replenish");

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('used_spots'));
    }

    public function test_deletes_batch(): void
    {
        $batch = SpotBatch::create(['total_spots' => 100, 'used_spots' => 0]);

        $response = $this->deleteJson("/api/superadmin/spots/{$batch->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('spot_batches', ['id' => $batch->id]);
    }

    public function test_stats_returns_global_counts(): void
    {
        SpotBatch::create(['total_spots' => 100, 'used_spots' => 30]);
        SpotBatch::create(['total_spots' => 50, 'used_spots' => 10]);

        $response = $this->getJson('/api/superadmin/spots/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure(['total_spots', 'used_spots', 'remaining']);
        $this->assertGreaterThanOrEqual(150, $response->json('total_spots'));
        $this->assertGreaterThanOrEqual(40, $response->json('used_spots'));
    }

    public function test_non_superadmin_gets_403(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);

        $tenantUser = User::create([
            'name' => 'Tenant',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        Sanctum::actingAs($tenantUser);

        $response = $this->getJson('/api/superadmin/spots');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        auth()->forgetGuards();

        $response = $this->getJson('/api/superadmin/spots');
        $response->assertStatus(401);
    }
}
