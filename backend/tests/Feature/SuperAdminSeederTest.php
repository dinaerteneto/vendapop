<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_superadmin_with_correct_attributes(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $user = User::where('email', 'superadmin@popvenda.com.br')->first();

        $this->assertNotNull($user);
        $this->assertEquals('Super Admin', $user->name);
        $this->assertTrue($user->is_super_admin);
        $this->assertNull($user->tenant_id);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $count = User::where('email', 'superadmin@popvenda.com.br')->count();
        $this->assertEquals(1, $count);
    }

    public function test_seeder_does_not_throw_errors_on_repeat(): void
    {
        $this->seed(SuperAdminSeeder::class);
        // Running twice should not throw exceptions
        $this->seed(SuperAdminSeeder::class);

        $this->assertTrue(true);
    }
}
