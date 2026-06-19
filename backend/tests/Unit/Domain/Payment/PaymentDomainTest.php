<?php

namespace Tests\Unit\Domain\Payment;

use App\Domain\Payment\CheckoutResponse;
use App\Domain\Payment\CreateCheckoutRequest;
use App\Domain\Payment\PaymentGateway;
use App\Domain\Payment\PaymentNotification;
use App\Domain\Payment\PaymentStatus;
use App\Domain\Payment\RefundRequest;
use App\Domain\Payment\RefundResponse;
use PHPUnit\Framework\TestCase;

class PaymentDomainTest extends TestCase
{
    public function test_enum_cases_exist_and_backed_values_are_correct(): void
    {
        $this->assertSame('pending', PaymentStatus::Pending->value);
        $this->assertSame('approved', PaymentStatus::Approved->value);
        $this->assertSame('rejected', PaymentStatus::Rejected->value);
        $this->assertSame('refunded', PaymentStatus::Refunded->value);
        $this->assertSame('cancelled', PaymentStatus::Cancelled->value);
    }

    public function test_create_checkout_request_constructor(): void
    {
        $request = new CreateCheckoutRequest(
            plan_type: 'premium',
            tenant_id: 42,
            return_url: 'https://example.com/success',
            cancel_url: 'https://example.com/cancel',
        );

        $this->assertSame('premium', $request->plan_type);
        $this->assertSame(42, $request->tenant_id);
        $this->assertSame('https://example.com/success', $request->return_url);
        $this->assertSame('https://example.com/cancel', $request->cancel_url);
    }

    public function test_create_checkout_request_from_array(): void
    {
        $request = CreateCheckoutRequest::fromArray([
            'plan_type' => 'basic',
            'tenant_id' => 'uuid-123',
            'return_url' => 'https://example.com/ok',
            'cancel_url' => 'https://example.com/back',
        ]);

        $this->assertSame('basic', $request->plan_type);
        $this->assertSame('uuid-123', $request->tenant_id);
    }

    public function test_checkout_response_constructor(): void
    {
        $expiresAt = new \DateTime('2026-12-31 23:59:59');
        $response = new CheckoutResponse(
            checkout_url: 'https://checkout.example.com/pay',
            transaction_id: 'txn_abc123',
            expires_at: $expiresAt,
        );

        $this->assertSame('https://checkout.example.com/pay', $response->checkout_url);
        $this->assertSame('txn_abc123', $response->transaction_id);
        $this->assertSame($expiresAt, $response->expires_at);
    }

    public function test_checkout_response_nullable_expires_at(): void
    {
        $response = new CheckoutResponse(
            checkout_url: 'https://checkout.example.com/pay',
            transaction_id: 'txn_abc123',
        );

        $this->assertNull($response->expires_at);
    }

    public function test_checkout_response_from_array(): void
    {
        $response = CheckoutResponse::fromArray([
            'checkout_url' => 'https://checkout.example.com/pay',
            'transaction_id' => 'txn_abc123',
            'expires_at' => '2026-12-31 23:59:59',
        ]);

        $this->assertSame('https://checkout.example.com/pay', $response->checkout_url);
        $this->assertSame('txn_abc123', $response->transaction_id);
        $this->assertInstanceOf(\DateTime::class, $response->expires_at);
    }

    public function test_payment_notification_constructor(): void
    {
        $paidAt = new \DateTime('2026-06-15 10:30:00');
        $notification = new PaymentNotification(
            transaction_id: 'txn_xyz',
            status: 'approved',
            external_reference: 'ref_001',
            paid_at: $paidAt,
        );

        $this->assertSame('txn_xyz', $notification->transaction_id);
        $this->assertSame('approved', $notification->status);
        $this->assertSame('ref_001', $notification->external_reference);
        $this->assertSame($paidAt, $notification->paid_at);
    }

    public function test_payment_notification_nullable_fields(): void
    {
        $notification = new PaymentNotification(
            transaction_id: 'txn_xyz',
            status: 'pending',
        );

        $this->assertNull($notification->external_reference);
        $this->assertNull($notification->paid_at);
    }

    public function test_refund_request_constructor(): void
    {
        $request = new RefundRequest(
            transaction_id: 'txn_refund',
            amount: 99.90,
        );

        $this->assertSame('txn_refund', $request->transaction_id);
        $this->assertSame(99.90, $request->amount);
    }

    public function test_refund_request_nullable_amount(): void
    {
        $request = new RefundRequest(transaction_id: 'txn_refund');

        $this->assertNull($request->amount);
    }

    public function test_refund_response_constructor(): void
    {
        $refundedAt = new \DateTime('2026-06-18 14:00:00');
        $response = new RefundResponse(
            transaction_id: 'txn_refund',
            refunded: true,
            refunded_at: $refundedAt,
            amount: 99.90,
        );

        $this->assertSame('txn_refund', $response->transaction_id);
        $this->assertTrue($response->refunded);
        $this->assertSame($refundedAt, $response->refunded_at);
        $this->assertSame(99.90, $response->amount);
    }

    public function test_refund_response_nullable_fields(): void
    {
        $response = new RefundResponse(
            transaction_id: 'txn_refund',
            refunded: false,
        );

        $this->assertFalse($response->refunded);
        $this->assertNull($response->refunded_at);
        $this->assertNull($response->amount);
    }

    public function test_payment_gateway_interface_can_be_implemented(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);

        $this->assertInstanceOf(PaymentGateway::class, $gateway);
    }

    public function test_payment_gateway_create_checkout_mock(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);
        $request = new CreateCheckoutRequest(
            plan_type: 'premium',
            tenant_id: 1,
            return_url: 'https://example.com/success',
            cancel_url: 'https://example.com/cancel',
        );

        $expectedResponse = new CheckoutResponse(
            checkout_url: 'https://checkout.example.com/pay',
            transaction_id: 'txn_mock',
        );

        $gateway->expects($this->once())
            ->method('createCheckout')
            ->with($this->equalTo($request))
            ->willReturn($expectedResponse);

        $result = $gateway->createCheckout($request);

        $this->assertSame($expectedResponse, $result);
    }

    public function test_payment_gateway_process_notification_mock(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);
        $notification = new PaymentNotification(
            transaction_id: 'txn_notify',
            status: 'approved',
        );

        $gateway->expects($this->once())
            ->method('processNotification')
            ->with($this->equalTo($notification));

        $gateway->processNotification($notification);
    }

    public function test_payment_gateway_refund_mock(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);
        $request = new RefundRequest(transaction_id: 'txn_refund', amount: 50.00);

        $expectedResponse = new RefundResponse(
            transaction_id: 'txn_refund',
            refunded: true,
            refunded_at: new \DateTime('2026-06-18 14:00:00'),
            amount: 50.00,
        );

        $gateway->expects($this->once())
            ->method('refund')
            ->with($this->equalTo($request))
            ->willReturn($expectedResponse);

        $result = $gateway->refund($request);

        $this->assertTrue($result->refunded);
        $this->assertSame(50.00, $result->amount);
    }
}
