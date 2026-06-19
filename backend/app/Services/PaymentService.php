<?php

namespace App\Services;

use App\Domain\Payment\CheckoutResponse;
use App\Domain\Payment\CreateCheckoutRequest;
use App\Domain\Payment\PaymentGatewayException;
use App\Domain\Payment\PaymentNotification;
use App\Domain\Payment\PaymentStatus;
use App\Domain\Payment\RefundRequest;
use App\Domain\Payment\RefundResponse;
use App\Infrastructure\Payment\PaymentGatewayFactory;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Psr\Log\LoggerInterface;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $factory,
        private SubscriptionService $subscriptionService,
        private LoggerInterface $logger,
    ) {}

    public function createCheckout(Subscription $subscription, string $planType, string $returnUrl, string $cancelUrl): CheckoutResponse
    {
        $gateway = $this->factory->make();

        $request = new CreateCheckoutRequest(
            plan_type: $planType,
            tenant_id: $subscription->tenant_id,
            return_url: $returnUrl,
            cancel_url: $cancelUrl,
        );

        try {
            $response = $gateway->createCheckout($request);

            PaymentTransaction::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $response->transaction_id,
                'plan_type' => $planType,
                'status' => PaymentStatus::Pending->value,
                'gateway' => config('services.payment.gateway', 'mercadopago'),
            ]);

            $this->subscriptionService->markPending($subscription);

            $this->logger->info('Checkout created', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'plan_type' => $planType,
                'transaction_id' => $response->transaction_id,
            ]);

            return $response;
        } catch (PaymentGatewayException $e) {
            $this->logger->error('Failed to create checkout', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'plan_type' => $planType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function handleNotification(PaymentNotification $notification): void
    {
        $transaction = PaymentTransaction::where('transaction_id', $notification->transaction_id)->first();

        if (!$transaction) {
            $this->logger->warning('Payment transaction not found for notification', [
                'transaction_id' => $notification->transaction_id,
            ]);
            return;
        }

        $transaction->update([
            'status' => $notification->status,
            'paid_at' => $notification->paid_at,
        ]);

        $gateway = $this->factory->make();
        $gateway->processNotification($notification);

        $subscription = $transaction->subscription;

        if (!$subscription) {
            $this->logger->warning('Subscription not found for transaction', [
                'transaction_id' => $notification->transaction_id,
            ]);
            return;
        }

        if ($notification->status === PaymentStatus::Approved->value) {
            $this->subscriptionService->upgradeTo(
                $subscription,
                $transaction->plan_type,
                $notification->transaction_id,
            );

            $this->logger->info('Payment approved, subscription upgraded', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $notification->transaction_id,
            ]);
        } elseif (in_array($notification->status, [
            PaymentStatus::Rejected->value,
            PaymentStatus::Cancelled->value,
        ])) {
            $this->subscriptionService->cancel($subscription);

            $this->logger->info('Payment rejected, subscription cancelled', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $notification->transaction_id,
                'status' => $notification->status,
            ]);
        }
    }

    public function refund(Subscription $subscription): RefundResponse
    {
        $transaction = PaymentTransaction::where('subscription_id', $subscription->id)
            ->where('status', PaymentStatus::Approved->value)
            ->latest()
            ->first();

        if (!$transaction) {
            throw new \RuntimeException('No approved transaction found for subscription');
        }

        $gateway = $this->factory->make();

        $request = new RefundRequest(
            transaction_id: $transaction->transaction_id,
        );

        try {
            $response = $gateway->refund($request);

            $transaction->update([
                'status' => PaymentStatus::Refunded->value,
            ]);

            $this->logger->info('Payment refunded', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->transaction_id,
            ]);

            return $response;
        } catch (PaymentGatewayException $e) {
            $this->logger->error('Failed to refund payment', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
