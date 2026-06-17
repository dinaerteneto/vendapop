<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Tenant $tenant;
    protected User $tenantUser;

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

        $this->tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-feedback',
            'whatsapp_number' => '5511999999999',
        ]);

        $this->tenantUser = User::create([
            'name' => 'Tenant User',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_tenant_submits_feedback(): void
    {
        Sanctum::actingAs($this->tenantUser);

        $response = $this->postJson('/api/admin/feedback', [
            'subject' => 'Bug report',
            'message' => 'Image upload is broken',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('subject', 'Bug report');
        $response->assertJsonPath('tenant_id', $this->tenant->id);

        $this->assertDatabaseHas('feedbacks', [
            'tenant_id' => $this->tenant->id,
            'subject' => 'Bug report',
            'status' => 'unread',
        ]);
    }

    public function test_feedback_validation_fails_without_subject(): void
    {
        Sanctum::actingAs($this->tenantUser);

        $response = $this->postJson('/api/admin/feedback', [
            'message' => 'Missing subject',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subject']);
    }

    public function test_feedback_validation_fails_without_message(): void
    {
        Sanctum::actingAs($this->tenantUser);

        $response = $this->postJson('/api/admin/feedback', [
            'subject' => 'Missing message',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_unauthenticated_cannot_submit_feedback(): void
    {
        $response = $this->postJson('/api/admin/feedback', [
            'subject' => 'Test',
            'message' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function test_superadmin_lists_all_feedbacks(): void
    {
        Feedback::create([
            'tenant_id' => $this->tenant->id,
            'subject' => 'Feedback 1',
            'message' => 'Message 1',
        ]);

        Feedback::create([
            'tenant_id' => $this->tenant->id,
            'subject' => 'Feedback 2',
            'message' => 'Message 2',
            'status' => 'resolved',
        ]);

        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/superadmin/feedbacks');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_superadmin_filters_feedback_by_status(): void
    {
        Feedback::create([
            'tenant_id' => $this->tenant->id, 'subject' => 'Unread', 'message' => 'M', 'status' => 'unread',
        ]);
        Feedback::create([
            'tenant_id' => $this->tenant->id, 'subject' => 'Resolved', 'message' => 'M', 'status' => 'resolved',
        ]);

        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/superadmin/feedbacks?status=resolved');

        $response->assertStatus(200);
        foreach ($response->json('data') as $item) {
            $this->assertEquals('resolved', $item['status']);
        }
    }

    public function test_superadmin_marks_feedback_as_read(): void
    {
        $feedback = Feedback::create([
            'tenant_id' => $this->tenant->id, 'subject' => 'Read me', 'message' => 'M',
        ]);

        Sanctum::actingAs($this->superAdmin);

        $response = $this->putJson("/api/superadmin/feedbacks/{$feedback->id}", [
            'status' => 'read',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('read', $response->json('status'));
    }

    public function test_superadmin_marks_feedback_as_resolved(): void
    {
        $feedback = Feedback::create([
            'tenant_id' => $this->tenant->id, 'subject' => 'Fix me', 'message' => 'M',
        ]);

        Sanctum::actingAs($this->superAdmin);

        $response = $this->putJson("/api/superadmin/feedbacks/{$feedback->id}", [
            'status' => 'resolved',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('resolved', $response->json('status'));
    }
}
