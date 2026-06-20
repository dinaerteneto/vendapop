<?php

namespace App\Infrastructure\Payment\Adapters;

use App\Domain\Payment\CheckoutResponse;
use App\Domain\Payment\CreateCheckoutRequest;
use App\Domain\Payment\PaymentGateway;
use App\Domain\Payment\PaymentGatewayException;
use App\Domain\Payment\PaymentNotification;
use App\Domain\Payment\RefundRequest;
use App\Domain\Payment\RefundResponse;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Psr\Log\LoggerInterface;

class MercadoPagoAdapter implements PaymentGateway
{
    private MercadoPagoSdkClient $sdkClient;
    private LoggerInterface $logger;

    private array $pricing = [
        'basic_monthly' => ['title' => 'Plano Básico (Mensal)', 'price' => 29.90],
        'basic_yearly' => ['title' => 'Plano Básico (Anual)', 'price' => 299.00],
        'professional_monthly' => ['title' => 'Plano Profissional (Mensal)', 'price' => 59.90],
        'professional_yearly' => ['title' => 'Plano Profissional (Anual)', 'price' => 599.00],
        'premium_monthly' => ['title' => 'Plano Premium (Mensal)', 'price' => 99.90],
        'premium_yearly' => ['title' => 'Plano Premium (Anual)', 'price' => 999.00],
    ];

    public function __construct(
        ?MercadoPagoSdkClient $sdkClient = null,
        ?LoggerInterface $logger = null,
    ) {
        $accessToken = config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            throw PaymentGatewayException::missingCredentials();
        }

        MercadoPagoConfig::setAccessToken($accessToken);

        $this->sdkClient = $sdkClient ?? new MercadoPagoSdkClientImpl();
        $this->logger = $logger ?? app(LoggerInterface::class);
    }

    public function createCheckout(CreateCheckoutRequest $request): CheckoutResponse
    {
        try {
            $plan = $this->getPlanPricing($request->plan_type);

            $isPublicUrl = !str_contains($request->return_url, 'localhost')
                && !str_contains($request->return_url, '127.0.0.1');

            $preferenceData = [
                'items' => [
                    [
                        'title' => $plan['title'],
                        'quantity' => 1,
                        'unit_price' => $plan['price'],
                        'currency_id' => 'BRL',
                    ],
                ],
                'payer' => [
                    'email' => '',
                ],
                'back_urls' => [
                    'success' => $request->return_url,
                    'failure' => $request->cancel_url,
                    'pending' => $request->return_url,
                ],
                'external_reference' => (string) $request->tenant_id,
                'metadata' => [
                    'plan_type' => $request->plan_type,
                    'tenant_id' => $request->tenant_id,
                ],
            ];

            if ($isPublicUrl) {
                $preferenceData['auto_return'] = 'approved';
            }

            $preference = $this->sdkClient->createPreference($preferenceData);

            return new CheckoutResponse(
                checkout_url: $preference->init_point,
                transaction_id: $preference->id,
            );
        } catch (PaymentGatewayException $e) {
            throw $e;
        } catch (MPApiException $e) {
            $this->logger->error('MercadoPago API error creating checkout', [
                'status' => $e->getStatusCode(),
                'response' => $e->getApiResponse()->getContent(),
                'plan_type' => $request->plan_type,
            ]);
            throw PaymentGatewayException::apiError('Failed to create MercadoPago preference.', $e);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error creating MercadoPago checkout', [
                'error' => $e->getMessage(),
                'plan_type' => $request->plan_type,
            ]);
            throw PaymentGatewayException::apiError('Unexpected error creating checkout.', $e);
        }
    }

    public function processNotification(PaymentNotification $notification): PaymentNotification
    {
        try {
            $paymentId = (int) $notification->transaction_id;
            $payment = $this->sdkClient->getPayment($paymentId);

            $this->logger->info('MercadoPago payment notification processed', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
            ]);

            return new PaymentNotification(
                transaction_id: $notification->transaction_id,
                status: $payment->status,
                external_reference: $payment->external_reference,
                paid_at: $payment->status === 'approved' && isset($payment->date_approved) 
                    ? new \DateTime($payment->date_approved) 
                    : null,
            );
        } catch (MPApiException $e) {
            $this->logger->error('MercadoPago API error processing notification', [
                'transaction_id' => $notification->transaction_id,
                'status' => $e->getStatusCode(),
                'response' => $e->getApiResponse()->getContent(),
            ]);
            throw PaymentGatewayException::apiError('Failed to process MercadoPago notification.', $e);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error processing MercadoPago notification', [
                'transaction_id' => $notification->transaction_id,
                'error' => $e->getMessage(),
            ]);
            throw PaymentGatewayException::apiError('Unexpected error processing notification.', $e);
        }
    }

    public function refund(RefundRequest $request): RefundResponse
    {
        try {
            $paymentId = (int) $request->transaction_id;

            if ($request->amount !== null) {
                $this->sdkClient->refundPartial($paymentId, $request->amount);
            } else {
                $this->sdkClient->refundTotal($paymentId);
            }

            return new RefundResponse(
                transaction_id: $request->transaction_id,
                refunded: true,
                refunded_at: new \DateTime(),
                amount: $request->amount,
            );
        } catch (MPApiException $e) {
            $this->logger->error('MercadoPago API error refunding payment', [
                'transaction_id' => $request->transaction_id,
                'status' => $e->getStatusCode(),
                'response' => $e->getApiResponse()->getContent(),
            ]);
            throw PaymentGatewayException::apiError('Failed to refund MercadoPago payment.', $e);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error refunding MercadoPago payment', [
                'transaction_id' => $request->transaction_id,
                'error' => $e->getMessage(),
            ]);
            throw PaymentGatewayException::apiError('Unexpected error refunding payment.', $e);
        }
    }

    private function getPlanPricing(string $planType): array
    {
        if (!isset($this->pricing[$planType])) {
            throw PaymentGatewayException::invalidPlan($planType);
        }

        return $this->pricing[$planType];
    }
}
