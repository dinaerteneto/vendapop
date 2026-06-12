<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_fails_without_terms_accepted(): void
    {
        $response = $this->postJson('/api/admin/register', [
            'store_name' => 'Test Store',
            'store_slug' => 'test-store',
            'whatsapp_number' => '5511999999999',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_registration_fails_with_terms_accepted_false(): void
    {
        $response = $this->postJson('/api/admin/register', [
            'store_name' => 'Test Store',
            'store_slug' => 'test-store-2',
            'whatsapp_number' => '5511999999999',
            'email' => 'test2@example.com',
            'terms_accepted' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_registration_succeeds_with_terms_accepted(): void
    {
        $response = $this->postJson('/api/admin/register', [
            'store_name' => 'Test Store',
            'store_slug' => 'test-store-3',
            'whatsapp_number' => '5511999999999',
            'email' => 'test3@example.com',
            'terms_accepted' => true,
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'test3@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->terms_accepted_at);
    }
}
