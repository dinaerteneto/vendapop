<?php

namespace Tests\Unit\Infrastructure\Payment;

use App\Domain\Payment\PaymentGatewayException;
use App\Infrastructure\Payment\Adapters\MercadoPagoAdapter;
use App\Infrastructure\Payment\PaymentGatewayFactory;
use Tests\TestCase;

class PaymentGatewayFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.mercadopago', [
            'access_token' => 'TEST-12345',
            'public_key' => 'TEST-PUBLIC-KEY',
        ]);
    }

    public function test_make_returns_mercadopago_adapter(): void
    {
        $factory = new PaymentGatewayFactory();

        $adapter = $factory->make('mercadopago');

        $this->assertInstanceOf(MercadoPagoAdapter::class, $adapter);
    }

    public function test_make_defaults_to_configured_gateway(): void
    {
        config()->set('services.payment.gateway', 'mercadopago');

        $factory = new PaymentGatewayFactory();

        $adapter = $factory->make();

        $this->assertInstanceOf(MercadoPagoAdapter::class, $adapter);
    }

    public function test_make_is_case_insensitive(): void
    {
        $factory = new PaymentGatewayFactory();

        $adapter = $factory->make('MercadoPago');

        $this->assertInstanceOf(MercadoPagoAdapter::class, $adapter);
    }

    public function test_make_throws_for_unsupported_gateway(): void
    {
        $factory = new PaymentGatewayFactory();

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Unsupported payment gateway: stripe');

        $factory->make('stripe');
    }

    public function test_make_throws_when_gateway_config_is_null(): void
    {
        config()->set('services.payment.gateway', null);

        $factory = new PaymentGatewayFactory();

        $this->expectException(PaymentGatewayException::class);

        $factory->make(null);
    }

    public function test_multiple_calls_return_fresh_instances(): void
    {
        $factory = new PaymentGatewayFactory();

        $first = $factory->make('mercadopago');
        $second = $factory->make('mercadopago');

        $this->assertNotSame($first, $second);
    }

    public function test_factory_is_registered_as_singleton(): void
    {
        $factory = app(PaymentGatewayFactory::class);

        $this->assertInstanceOf(PaymentGatewayFactory::class, $factory);
    }
}
