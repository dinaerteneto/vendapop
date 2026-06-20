<?php

namespace Tests\Unit\UseCases\Payment;

use App\Domain\Payment\CheckoutResponse;
use App\Domain\Payment\PaymentNotification;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use App\UseCases\Payment\CancelSubscriptionUseCase;
use App\UseCases\Payment\CreateCheckoutUseCase;
use App\UseCases\Payment\HandleWebhookUseCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class PaymentUseCasesTest extends TestCase
{
    use DatabaseTransactions;

    private PaymentService|Mockery\MockInterface $paymentService;
    private SubscriptionService|Mockery\MockInterface $subscriptionService;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = Mockery::mock(PaymentService::class);
        $this->subscriptionService = Mockery::mock(SubscriptionService::class);

        $this->tenant = Tenant::create([
            'name' => 'UseCase Test',
            'slug' => 'uc-test-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_checkout_use_case_calls_payment_service(): void
    {
        $subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        $expectedResponse = new CheckoutResponse(
            checkout_url: 'https://checkout.example.com/pay',
            transaction_id: 'txn_use_case',
        );

        $this->paymentService
            ->shouldReceive('createCheckout')
            ->once()
            ->with(
                Mockery::on(fn ($s) => $s->id === $subscription->id),
                'professional',
                'https://example.com/success',
                'https://example.com/cancel',
            )
            ->andReturn($expectedResponse);

        $useCase = new CreateCheckoutUseCase($this->paymentService);

        $response = $useCase->execute(
            subscriptionId: $subscription->id,
            planType: 'professional',
            returnUrl: 'https://example.com/success',
            cancelUrl: 'https://example.com/cancel',
        );

        $this->assertSame($expectedResponse, $response);
        $this->assertSame('https://checkout.example.com/pay', $response->checkout_url);
    }

    public function test_handle_webhook_use_case_calls_payment_service(): void
    {
        $notification = new PaymentNotification(
            transaction_id: 'txn_webhook',
            status: 'approved',
            paid_at: now(),
        );

        $this->paymentService
            ->shouldReceive('handleNotification')
            ->once()
            ->with(Mockery::on(fn ($n) => $n->transaction_id === 'txn_webhook'))
            ->andReturnNull();

        $useCase = new HandleWebhookUseCase($this->paymentService);

        $useCase->execute($notification);

        $this->expectNotToPerformAssertions();
    }

    public function test_cancel_subscription_use_case_calls_subscription_service(): void
    {
        $subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'professional',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        $expected = $subscription->fresh();
        $expected->plan_status = 'cancelled';

        $this->subscriptionService
            ->shouldReceive('cancel')
            ->once()
            ->with(Mockery::on(fn ($s) => $s->id === $subscription->id))
            ->andReturn($expected);

        $useCase = new CancelSubscriptionUseCase($this->subscriptionService);

        $result = $useCase->execute($subscription->id);

        $this->assertSame('cancelled', $result->plan_status);
        $this->assertSame($subscription->id, $result->id);
    }

    public function test_create_checkout_throws_when_subscription_not_found(): void
    {
        $useCase = new CreateCheckoutUseCase($this->paymentService);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $useCase->execute(
            subscriptionId: 999999,
            planType: 'professional',
            returnUrl: 'https://example.com/success',
            cancelUrl: 'https://example.com/cancel',
        );
    }

    public function test_cancel_subscription_throws_when_subscription_not_found(): void
    {
        $useCase = new CancelSubscriptionUseCase($this->subscriptionService);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $useCase->execute(999999);
    }

    public function test_handle_webhook_use_case_passes_correct_notification(): void
    {
        $paidAt = new \DateTime('2026-06-19 10:00:00');

        $notification = new PaymentNotification(
            transaction_id: 'txn_webhook_2',
            status: 'rejected',
            external_reference: 'ref_002',
            paid_at: $paidAt,
        );

        $this->paymentService
            ->shouldReceive('handleNotification')
            ->once()
            ->with(Mockery::on(function ($n) use ($paidAt) {
                return $n->transaction_id === 'txn_webhook_2'
                    && $n->status === 'rejected'
                    && $n->external_reference === 'ref_002'
                    && $n->paid_at == $paidAt;
            }))
            ->andReturnNull();

        $useCase = new HandleWebhookUseCase($this->paymentService);

        $useCase->execute($notification);

        $this->expectNotToPerformAssertions();
    }
}
