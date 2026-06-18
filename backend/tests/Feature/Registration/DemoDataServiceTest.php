<?php

namespace Tests\Feature\Registration;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DemoDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'store_name' => 'Minha Loja',
            'store_slug' => 'minha-loja-' . uniqid(),
            'whatsapp_number' => '5511999999999',
            'email' => uniqid() . '@example.com',
            'terms_accepted' => true,
        ];
    }

    public function test_registration_creates_demo_categories(): void
    {
        Mail::fake();

        $this->postJson('/api/admin/register', $this->validPayload());

        $tenant = Tenant::latest()->first();
        $this->assertNotNull($tenant);
        $this->assertCount(2, $tenant->categories);
        $this->assertTrue($tenant->categories->every(fn($c) => $c->is_demo));
    }

    public function test_registration_creates_four_demo_products(): void
    {
        Mail::fake();

        $this->postJson('/api/admin/register', $this->validPayload());

        $tenant = Tenant::latest()->first();
        $this->assertNotNull($tenant);
        $this->assertCount(4, $tenant->products);
        $this->assertTrue($tenant->products->every(fn($p) => $p->is_demo));
    }

    public function test_each_demo_product_has_main_image(): void
    {
        Mail::fake();

        $this->postJson('/api/admin/register', $this->validPayload());

        $tenant = Tenant::latest()->first();
        foreach ($tenant->products as $product) {
            $this->assertNotNull($product->images()->where('is_main', true)->first());
        }
    }

    public function test_registration_creates_demo_banner(): void
    {
        Mail::fake();

        $this->postJson('/api/admin/register', $this->validPayload());

        $tenant = Tenant::latest()->first();
        $this->assertCount(1, $tenant->rotatingBanners);
    }

    public function test_demo_data_failure_rolls_back_registration(): void
    {
        Mail::fake();

        $this->mock(\App\Services\DemoDataService::class, function ($mock) {
            $mock->shouldReceive('seedFor')->andThrow(new \Exception('fail'));
        });

        $payload = $this->validPayload();
        $this->postJson('/api/admin/register', $payload);

        $this->assertDatabaseMissing('tenants', ['slug' => $payload['store_slug']]);
    }
}
