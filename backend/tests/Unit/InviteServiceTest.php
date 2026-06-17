<?php

namespace Tests\Unit;

use App\Models\Invite;
use App\Models\Tenant;
use App\Services\InviteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InviteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InviteService $inviteService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inviteService = new InviteService();
    }

    protected function createTenant(string $slug = 'test-store'): Tenant
    {
        return Tenant::create([
            'name' => 'Test Store',
            'slug' => $slug,
            'whatsapp_number' => '5511999999999',
        ]);
    }

    public function test_generate_manual_creates_invite_with_correct_fields(): void
    {
        $tenant = $this->createTenant('manual-test');

        $invites = $this->inviteService->generateManual($tenant, 1);

        $this->assertCount(1, $invites);
        $invite = $invites[0];
        $this->assertEquals('manual', $invite->type);
        $this->assertEquals($tenant->id, $invite->created_by_tenant_id);
        $this->assertEquals(1, $invite->max_uses);
        $this->assertEquals(0, $invite->current_uses);
        $this->assertEquals(8, strlen($invite->code));
        $this->assertTrue($invite->expires_at->isFuture());
    }

    public function test_generate_manual_creates_unique_codes(): void
    {
        $tenant = $this->createTenant('unique-test');

        $invites = $this->inviteService->generateManual($tenant, 2);

        $this->assertCount(2, $invites);
        $this->assertNotEquals($invites[0]->code, $invites[1]->code);
    }

    public function test_create_public_link_with_correct_fields(): void
    {
        $invite = $this->inviteService->createPublicLink(5, 48);

        $this->assertEquals('public', $invite->type);
        $this->assertNull($invite->created_by_tenant_id);
        $this->assertEquals(5, $invite->max_uses);
        $this->assertEquals(0, $invite->current_uses);
        $this->assertEquals(8, strlen($invite->code));
        $this->assertTrue($invite->expires_at->isFuture());
    }

    public function test_validate_returns_invite_for_valid_code(): void
    {
        $invite = $this->inviteService->createPublicLink(3);

        $validated = $this->inviteService->validate($invite->code);

        $this->assertEquals($invite->id, $validated->id);
    }

    public function test_validate_throws_for_nonexistent_code(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('inválido');

        $this->inviteService->validate('DOESNTEXIST');
    }

    public function test_validate_throws_for_expired_code(): void
    {
        $tenant = $this->createTenant('expire-test');
        $invite = Invite::create([
            'code' => 'EXPIRED1',
            'type' => 'manual',
            'created_by_tenant_id' => $tenant->id,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('expirou');

        $this->inviteService->validate($invite->code);
    }

    public function test_validate_throws_for_exhausted_code(): void
    {
        $tenant = $this->createTenant('exhaust-test');
        $invite = Invite::create([
            'code' => 'FULLSLOT',
            'type' => 'public',
            'max_uses' => 2,
            'current_uses' => 2,
            'expires_at' => now()->addDays(7),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Vagas esgotadas');

        $this->inviteService->validate($invite->code);
    }

    public function test_consume_increments_current_uses(): void
    {
        $tenant = $this->createTenant('consume-test');
        $invite = $this->inviteService->createPublicLink(5);

        $this->inviteService->consume($invite, $tenant);

        $this->assertEquals(1, $invite->fresh()->current_uses);
    }

    public function test_remaining_for_tenant_returns_correct_count(): void
    {
        $tenant = $this->createTenant('limit-test');

        $this->assertEquals(2, $this->inviteService->remainingForTenant($tenant));

        $this->inviteService->generateManual($tenant, 1);
        $this->assertEquals(1, $this->inviteService->remainingForTenant($tenant));

        $this->inviteService->generateManual($tenant, 1);
        $this->assertEquals(0, $this->inviteService->remainingForTenant($tenant));
    }
}
