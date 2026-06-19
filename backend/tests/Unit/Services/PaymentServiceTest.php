<?php

namespace Tests\Unit\Services;

use App\Domain\Payment\CheckoutResponse;
use App\Domain\Payment\CreateCheckoutRequest;
use App\Domain\Payment\PaymentGateway;
use App\Domain\Payment\PaymentGatewayException;
use App\Domain\Payment\PaymentNotification;
use App\Domain\Payment\RefundRequest;
use App\Domain\Payment\RefundResponse;
use App\Infrastructure\Payment\PaymentGatewayFactory;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentGatewayFactory|Mockery\MockInterface $factory;
    private PaymentGateway|Mockery\MockInterface $gateway;
    private SubscriptionService|Mockery\MockInterface $subscriptionService;
    private LoggerInterface|Mockery\MockInterface $logger;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = Mockery::mock(PaymentGateway::class);
        $this->factory = Mockery::mock(PaymentGatewayFactory::class);
        $this->factory->allows()->make()->andReturn($this->gateway);

        $this->subscriptionService = Mockery::mock(SubscriptionService::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->paymentService = new PaymentService(
            factory: $this->factory,
            subscriptionService: $this->subscriptionService,
            logger: $this->logger,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_checkout_stores_transaction_and_returns_response(): void
    {
        $txnId = 'txn_c1_' . uniqid();
        $slug = 'test-c1-' . uniqid();

        $tenant = Tenant::create([
            'name' => 'Test',
            'slug' => $slug,
            'whatsapp_number' => '123456789',
        ]);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        $expectedResponse = new CheckoutResponse(
            checkout_url: 'https://checkout.example.com/pay',
            transaction_id: $txnId,
        );

        $this->gateway->shouldReceive('createCheckout')
            ->once()
            ->with(Mockery::type(CreateCheckoutRequest::class))
            ->andReturn($expectedResponse);

        $this->subscriptionService->shouldReceive('markPending')
            ->once()
            ->with(Mockery::on(fn ($s) => $s->id === $subscription->id))
            ->andReturn($subscription);

        $this->logger->shouldReceive('info')
            ->once()
            ->with('Checkout created', Mockery::any());

        $response = $this->paymentService->createCheckout(
            subscription: $subscription,
            planType: 'basic_monthly',
            returnUrl: 'https://example.com/success',
            cancelUrl: 'https://example.com/cancel',
        );

        $this->assertSame($expectedResponse, $response);
        $this->assertSame('https://checkout.example.com/pay', $response->checkout_url);
        $this->assertSame($txnId, $response->transaction_id);

        $this->assertDatabaseHas('payment_transactions', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => $txnId,
            'plan_type' => 'basic_monthly',
            'status' => 'pending',
        ]);
    }

    public function test_create_checkout_logs_error_and_throws_on_failure(): void
    {
        $slug = 'test-e1-' . uniqid();

        $tenant = Tenant::create([
            'name' => 'Test',
            'slug' => $slug,
            'whatsapp_number' => '123456789',
        ]);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        $exception = PaymentGatewayException::apiError('Gateway API error');

        $this->gateway->shouldReceive('createCheckout')
            ->once()
            ->andThrow($exception);

        $this->logger->shouldReceive('error')
            ->once()
            ->with('Failed to create checkout', Mockery::any());

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Gateway API error');

        $this->paymentService->createCheckout(
            subscription: $subscription,
            planType: 'basic_monthly',
            returnUrl: 'https://example.com/success',
            cancelUrl: 'https://example.com/cancel',
        );
    }

    public function test_handle_notification_with_approved_status_upgrades_subscription(): void
    {
        $txnId = 'txn_a1_' . uniqid();
        $slug = 'test-a1-' . uniqid();

        $tenant = Tenant::create([
            'name' => 'Test',
            'slug' => $slug,
            'whatsapp_number' => '123456789',
        ]);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => $txnId,
            'plan_type' => 'basic_monthly',
            'status' => 'pending',
            'gateway' => 'mercadopago',
        ]);

        $notification = new PaymentNotification(
            transaction_id: $txnId,
            status: 'approved',
            paid_at: now(),
        );

        $this->gateway->shouldReceive('processNotification')
            ->once()
            ->with($notification);

        $this->subscriptionService->shouldReceive('upgradeTo')
            ->once()
            ->with(Mockery::on(fn ($s) => $s->id === $subscription->id), 'basic_monthly', $txnId);

        $this->logger->shouldReceive('info')
            ->once()
            ->with('Payment approved, subscription upgraded', Mockery::any());

        $this->paymentService->handleNotification($notification);

        $this->assertDatabaseHas('payment_transactions', [
            'transaction_id' => $txnId,
            'status' => 'approved',
        ]);
    }

    public function test_handle_notification_with_rejected_status_marks_cancelled(): void
    {
        $txnId = 'txn_r1_' . uniqid();
        $slug = 'test-r1-' . uniqid();

        $tenant = Tenant::create([
            'name' => 'Test',
            'slug' => $slug,
            'whatsapp_number' => '123456789',
        ]);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => $txnId,
            'plan_type' => 'basic_monthly',
            'status' => 'pending',
            'gateway' => 'mercadopago',
        ]);

        $notification = new PaymentNotification(
            transaction_id: $txnId,
            status: 'rejected',
        );

        $this->gateway->shouldReceive('processNotification')
            ->once()
            ->with($notification);

        $this->subscriptionService->shouldReceive('cancel')
            ->once()
            ->with(Mockery::on(fn ($s) => $s->id === $subscription->id));

        $this->logger->shouldReceive('info')
            ->once()
            ->with('Payment rejected, subscription cancelled', Mockery::any());

        $this->paymentService->handleNotification($notification);

        $this->assertDatabaseHas('payment_transactions', [
            'transaction_id' => $txnId,
            'status' => 'rejected',
        ]);
    }

    public function test_refund_calls_gateway_refund_and_updates_status(): void
    {
        $txnId = 'txn_rf1_' . uniqid();
        $slug = 'test-rf1-' . uniqid();

        $tenant = Tenant::create([
            'name' => 'Test',
            'slug' => $slug,
            'whatsapp_number' => '123456789',
        ]);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => $txnId,
            'plan_type' => 'basic_monthly',
            'status' => 'approved',
            'gateway' => 'mercadopago',
            'paid_at' => now(),
        ]);

        $expectedResponse = new RefundResponse(
            transaction_id: $txnId,
            refunded: true,
            refunded_at: new \DateTime(),
            amount: 29.90,
        );

        $this->gateway->shouldReceive('refund')
            ->once()
            ->with(Mockery::type(RefundRequest::class))
            ->andReturn($expectedResponse);

        $this->logger->shouldReceive('info')
            ->once()
            ->with('Payment refunded', Mockery::any());

        $response = $this->paymentService->refund($subscription);

        $this->assertTrue($response->refunded);
        $this->assertSame($txnId, $response->transaction_id);

        $this->assertDatabaseHas('payment_transactions', [
            'transaction_id' => $txnId,
            'status' => 'refunded',
        ]);
    }
}
