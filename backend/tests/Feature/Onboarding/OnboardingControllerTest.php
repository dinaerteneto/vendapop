<?php

namespace Tests\Feature\Onboarding;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithTenant(): User
    {
        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    public function test_update_onboarding_step(): void
    {
        $user = $this->createUserWithTenant();
        Sanctum::actingAs($user);

        $this->putJson('/api/admin/onboarding-status', ['onboarding_step' => 2])
             ->assertOk()
             ->assertJsonPath('tenant.onboarding_step', 2);

        $this->assertEquals(2, $user->tenant->fresh()->onboarding_step);
    }

    public function test_update_onboarding_completed(): void
    {
        $user = $this->createUserWithTenant();
        Sanctum::actingAs($user);

        $this->putJson('/api/admin/onboarding-status', ['onboarding_completed' => true])
             ->assertOk();

        $this->assertTrue($user->tenant->fresh()->onboarding_completed);
    }

    public function test_onboarding_status_requires_auth(): void
    {
        $this->putJson('/api/admin/onboarding-status', ['onboarding_step' => 1])
             ->assertUnauthorized();
    }

    public function test_login_response_includes_onboarding_fields(): void
    {
        $user = $this->createUserWithTenant();

        $this->postJson('/api/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('tenant.onboarding_completed', false)
            ->assertJsonPath('tenant.onboarding_step', 0);
    }
}
