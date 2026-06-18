<?php

namespace Tests\Feature\Onboarding;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImageProxyControllerTest extends TestCase
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

    public function test_proxy_downloads_valid_image(): void
    {
        Storage::fake('public');

        Http::fake(['https://example.com/photo.jpg' => Http::response(
            file_get_contents(base_path('tests/fixtures/test-image.jpg')),
            200,
            ['Content-Type' => 'image/jpeg']
        )]);

        $user = $this->createUserWithTenant();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/photo.jpg'])
                         ->assertOk()
                         ->assertJsonStructure(['url', 'path']);

        Storage::disk('public')->assertExists($response->json('path'));
    }

    public function test_proxy_rejects_non_image_url(): void
    {
        Http::fake(['https://example.com/page.html' => Http::response('<html>', 200, ['Content-Type' => 'text/html'])]);

        $user = $this->createUserWithTenant();
        Sanctum::actingAs($user);

        $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/page.html'])
             ->assertUnprocessable()
             ->assertJsonFragment(['message' => 'O link não aponta para uma imagem válida.']);
    }

    public function test_proxy_handles_failed_request(): void
    {
        Http::fake(['*' => Http::response(null, 404)]);

        $user = $this->createUserWithTenant();
        Sanctum::actingAs($user);

        $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/missing.jpg'])
             ->assertUnprocessable();
    }

    public function test_proxy_requires_auth(): void
    {
        $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/photo.jpg'])
             ->assertUnauthorized();
    }
}
