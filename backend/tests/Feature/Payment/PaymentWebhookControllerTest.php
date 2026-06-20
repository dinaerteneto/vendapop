<?php

namespace Tests\Feature\Payment;

use App\Domain\Payment\PaymentNotification;
use App\UseCases\Payment\HandleWebhookUseCase;
use Tests\TestCase;

class PaymentWebhookControllerTest extends TestCase
{
    public function test_valid_webhook_returns_202(): void
    {
        $this->mock(HandleWebhookUseCase::class)
            ->shouldReceive('execute')
            ->once()
            ->andReturnNull();

        $payload = [
            'type' => 'payment',
            'data' => ['id' => 'mp_payment_123'],
        ];

        $response = $this->postJson('/api/webhooks/payment/mercadopago', $payload);

        $response->assertStatus(202)
            ->assertJson(['status' => 'accepted']);
    }

    public function test_invalid_type_returns_422(): void
    {
        $payload = [
            'type' => 'unknown',
            'data' => ['id' => '123'],
        ];

        $response = $this->postJson('/api/webhooks/payment/mercadopago', $payload);

        $response->assertStatus(422);
    }

    public function test_missing_data_id_returns_422(): void
    {
        $payload = [
            'type' => 'payment',
        ];

        $response = $this->postJson('/api/webhooks/payment/mercadopago', $payload);

        $response->assertStatus(422);
    }

    public function test_use_case_is_called_with_correct_transaction_id(): void
    {
        $mock = $this->mock(HandleWebhookUseCase::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with(\Mockery::on(function (PaymentNotification $notification) {
                return $notification->transaction_id === 'mp_payment_456';
            }));

        $payload = [
            'action' => 'payment.created',
            'data' => ['id' => 'mp_payment_456'],
        ];

        $this->postJson('/api/webhooks/payment/mercadopago', $payload)
            ->assertStatus(202);
    }
}
