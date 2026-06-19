<?php

namespace Tests\Unit\Infrastructure\Payment;

use App\Domain\Payment\CheckoutResponse;
use App\Domain\Payment\CreateCheckoutRequest;
use App\Domain\Payment\PaymentGatewayException;
use App\Domain\Payment\PaymentNotification;
use App\Domain\Payment\RefundRequest;
use App\Domain\Payment\RefundResponse;
use App\Infrastructure\Payment\Adapters\MercadoPagoAdapter;
use App\Infrastructure\Payment\Adapters\MercadoPagoSdkClient;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class MercadoPagoAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.mercadopago', [
            'access_token' => 'TEST-12345',
            'public_key' => 'TEST-PUBLIC-KEY',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_constructor_throws_exception_when_credentials_missing(): void
    {
        config()->set('services.mercadopago.access_token', null);

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Payment gateway credentials are not configured');

        new MercadoPagoAdapter();
    }

    public function test_create_checkout_returns_checkout_response(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('createPreference')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['items'][0]['unit_price'] === 29.90
                    && $data['items'][0]['title'] === 'Plano Básico (Mensal)'
                    && $data['auto_return'] === 'approved';
            }))
            ->andReturn((object) [
                'id' => 'pref_123',
                'init_point' => 'https://www.mercadopago.com.br/checkout/pref_123',
            ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $request = new CreateCheckoutRequest(
            plan_type: 'basic_monthly',
            tenant_id: 1,
            return_url: 'https://example.com/success',
            cancel_url: 'https://example.com/cancel',
        );

        $response = $adapter->createCheckout($request);

        $this->assertInstanceOf(CheckoutResponse::class, $response);
        $this->assertEquals('https://www.mercadopago.com.br/checkout/pref_123', $response->checkout_url);
        $this->assertEquals('pref_123', $response->transaction_id);
    }

    public function test_create_checkout_uses_correct_pricing_for_all_plans(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);

        $prices = [
            'basic_monthly' => 29.90,
            'basic_yearly' => 299.00,
            'professional_monthly' => 59.90,
            'professional_yearly' => 599.00,
            'premium_monthly' => 99.90,
            'premium_yearly' => 999.00,
        ];

        $callCount = 0;
        foreach ($prices as $planType => $expectedPrice) {
            $sdkClient->shouldReceive('createPreference')
                ->once()
                ->with(Mockery::on(function (array $data) use ($expectedPrice) {
                    return abs($data['items'][0]['unit_price'] - $expectedPrice) < 0.01;
                }))
                ->andReturn((object) [
                    'id' => 'pref_' . (int) ($expectedPrice * 100),
                    'init_point' => 'https://mp.com/checkout/pref_' . (int) ($expectedPrice * 100),
                ]);
        }

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        foreach ($prices as $planType => $expectedPrice) {
            $request = new CreateCheckoutRequest(
                plan_type: $planType,
                tenant_id: 1,
                return_url: 'https://example.com/success',
                cancel_url: 'https://example.com/cancel',
            );

            $response = $adapter->createCheckout($request);
            $this->assertNotNull($response->checkout_url);
        }
    }

    public function test_create_checkout_throws_exception_on_invalid_plan_type(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $logger = Mockery::mock(LoggerInterface::class);

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $request = new CreateCheckoutRequest(
            plan_type: 'nonexistent_plan',
            tenant_id: 1,
            return_url: 'https://example.com/success',
            cancel_url: 'https://example.com/cancel',
        );

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Invalid plan type: nonexistent_plan');

        $adapter->createCheckout($request);
    }

    public function test_create_checkout_throws_on_sdk_error(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('createPreference')
            ->once()
            ->andThrow(new \RuntimeException('API Error'));

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $request = new CreateCheckoutRequest(
            plan_type: 'basic_monthly',
            tenant_id: 1,
            return_url: 'https://example.com/success',
            cancel_url: 'https://example.com/cancel',
        );

        $this->expectException(PaymentGatewayException::class);

        $adapter->createCheckout($request);
    }

    public function test_process_notification_queries_payment_and_logs(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('getPayment')
            ->once()
            ->with(789)
            ->andReturn((object) [
                'id' => 789,
                'status' => 'approved',
                'status_detail' => 'accredited',
            ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with('MercadoPago payment notification processed', Mockery::any());
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $notification = new PaymentNotification(
            transaction_id: '789',
            status: 'approved',
        );

        $adapter->processNotification($notification);

        $this->assertTrue(true);
    }

    public function test_process_notification_throws_on_api_error(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('getPayment')
            ->once()
            ->with(999)
            ->andThrow(new \RuntimeException('Not Found'));

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $notification = new PaymentNotification(
            transaction_id: '999',
            status: 'pending',
        );

        $this->expectException(PaymentGatewayException::class);

        $adapter->processNotification($notification);
    }

    public function test_refund_returns_refund_response_on_total_refund(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('refundTotal')
            ->once()
            ->with(789)
            ->andReturnNull();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $request = new RefundRequest(transaction_id: '789');

        $response = $adapter->refund($request);

        $this->assertInstanceOf(RefundResponse::class, $response);
        $this->assertTrue($response->refunded);
        $this->assertEquals('789', $response->transaction_id);
    }

    public function test_refund_with_amount_calls_partial_refund(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('refundPartial')
            ->once()
            ->with(789, 50.00)
            ->andReturnNull();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $request = new RefundRequest(transaction_id: '789', amount: 50.00);

        $response = $adapter->refund($request);

        $this->assertTrue($response->refunded);
        $this->assertEquals(50.00, $response->amount);
    }

    public function test_refund_throws_on_api_error(): void
    {
        $sdkClient = Mockery::mock(MercadoPagoSdkClient::class);
        $sdkClient->shouldReceive('refundTotal')
            ->once()
            ->andThrow(new \RuntimeException('Refund Error'));

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows()->error(Mockery::any(), Mockery::any())->andReturnNull();

        $adapter = new MercadoPagoAdapter(sdkClient: $sdkClient, logger: $logger);

        $request = new RefundRequest(transaction_id: '789');

        $this->expectException(PaymentGatewayException::class);

        $adapter->refund($request);
    }
}
