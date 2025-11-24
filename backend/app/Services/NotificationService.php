<?php

namespace App\Services;

use App\Mail\NewOrderNotificationMail;
use App\Models\CustomerPushSubscription;
use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    private WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Notify administrators about a new order
     */
    public function notifyNewOrder(Order $order): void
    {
        $order->load(['customer', 'tenant', 'items']);
        $tenant = $order->tenant;

        // Get all administrators for this tenant
        $administrators = User::where('tenant_id', $tenant->id)->get();

        foreach ($administrators as $admin) {
            // 1. Send email notification
            $this->sendEmailNotification($admin, $order, $tenant);

            // 2. Send push notification
            $this->sendPushNotification($admin, $order, $tenant);

            // 3. Send WhatsApp notification (only once, to tenant's WhatsApp)
            if ($admin->is_owner) {
                $this->sendWhatsAppNotification($tenant, $order);
            }
        }
    }

    /**
     * Send email notification to administrator
     */
    private function sendEmailNotification(User $admin, Order $order, Tenant $tenant): void
    {
        try {
            $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
            $orderUrl = "{$frontendUrl}/admin/orders/{$order->uuid}";

            Mail::to($admin->email)->send(new NewOrderNotificationMail($order, $orderUrl));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de notificação de pedido: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification to administrator
     *
     * Note: This requires the minishlink/web-push package.
     * Install it with: composer require minishlink/web-push
     */
    private function sendPushNotification(User $admin, Order $order, Tenant $tenant): void
    {
        try {
            $subscriptions = PushSubscription::where('user_id', $admin->id)->get();

            if ($subscriptions->isEmpty()) {
                return;
            }

            $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
            $orderUrl = "{$frontendUrl}/admin/orders/{$order->uuid}";

            $payload = json_encode([
                'title' => 'Novo Pedido Recebido',
                'body' => "Pedido #{$order->order_number} de {$order->customer->name} - R$ " . number_format($order->total_amount, 2, ',', '.'),
                'icon' => '/icon-192x192.png',
                'badge' => '/icon-192x192.png',
                'url' => $orderUrl,
                'data' => [
                    'order_uuid' => $order->uuid,
                    'order_number' => $order->order_number,
                ],
            ]);

            $vapidPublicKey = env('VAPID_PUBLIC_KEY');
            $vapidPrivateKey = env('VAPID_PRIVATE_KEY');
            $vapidSubject = env('VAPID_SUBJECT', 'mailto:' . $admin->email);

            if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
                Log::warning('VAPID keys não configuradas. Push notifications não serão enviadas.');
                return;
            }

            // Check if web-push package is available
            if (!class_exists(\Minishlink\WebPush\WebPush::class)) {
                Log::warning('Biblioteca web-push não instalada. Execute: composer require minishlink/web-push');
                return;
            }

            $auth = [
                'VAPID' => [
                    'subject' => $vapidSubject,
                    'publicKey' => $vapidPublicKey,
                    'privateKey' => $vapidPrivateKey,
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            foreach ($subscriptions as $subscription) {
                try {
                    $webPushSubscription = \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'keys' => [
                            'p256dh' => $subscription->public_key,
                            'auth' => $subscription->auth_token,
                        ],
                    ]);

                    $webPush->queueNotification($webPushSubscription, $payload);
                } catch (\Exception $e) {
                    Log::error('Erro ao criar subscription para push: ' . $e->getMessage());
                }
            }

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    Log::error('Erro ao enviar push notification: ' . $report->getReason());
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar push notification: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to tenant
     */
    private function sendWhatsAppNotification(Tenant $tenant, Order $order): void
    {
        try {
            $frontendUrl = rtrim(config('services.frontend_url', 'http://localhost:5173'), '/');
            $trackingUrl = "{$frontendUrl}/{$tenant->slug}/order/{$order->uuid}";

            $message = "🔔 *Novo Pedido Recebido!*\n\n";
            $message .= "Pedido nº: *{$order->order_number}*\n";
            $message .= "Cliente: *{$order->customer->name}*\n";
            $message .= "Valor: *R$ " . number_format($order->total_amount, 2, ',', '.') . "*\n\n";
            $message .= "Link do pedido:\n{$trackingUrl}";

            $whatsappUrl = $this->whatsAppService->generateWhatsAppUrl($tenant, $message);

            // Log the WhatsApp URL (in production, you might want to actually send it via API)
            Log::info('WhatsApp notification URL gerada', [
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'whatsapp_url' => $whatsappUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar link do WhatsApp: ' . $e->getMessage());
        }
    }

    /**
     * Notify customer about order status change
     */
    public function notifyCustomerOrderStatus(Order $order): void
    {
        $order->load(['customer', 'tenant']);

        // Only notify for SENT and DONE statuses
        if (!in_array($order->status, ['SENT', 'DONE'])) {
            return;
        }

        $this->sendCustomerPushNotification($order);
    }

    /**
     * Send push notification to customer
     */
    private function sendCustomerPushNotification(Order $order): void
    {
        try {
            $subscriptions = CustomerPushSubscription::where('order_uuid', $order->uuid)->get();

            if ($subscriptions->isEmpty()) {
                return;
            }

            $frontendUrl = rtrim(config('services.frontend_url', 'http://localhost:5173'), '/');
            $orderUrl = "{$frontendUrl}/{$order->tenant->slug}/order/{$order->uuid}";

            $customerName = $order->customer->name;
            $orderNumber = $order->order_number;
            $storeName = $order->tenant->name;

            $statusMessages = [
                'SENT' => "Olá {$customerName}, seu pedido n. {$orderNumber} da loja {$storeName} foi enviado! 🚚",
                'DONE' => "Olá {$customerName}, seu pedido n. {$orderNumber} da loja {$storeName} foi concluído! ✅",
            ];

            $statusBodies = [
                'SENT' => "Seu pedido está a caminho! Acompanhe o rastreamento pelo link abaixo.",
                'DONE' => "Obrigado pela sua compra! Esperamos que tenha gostado dos produtos.",
            ];

            $title = $statusMessages[$order->status] ?? "Olá {$customerName}, seu pedido n. {$orderNumber} foi atualizado.";
            $body = $statusBodies[$order->status] ?? "Acompanhe o status do seu pedido pelo link abaixo.";

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/icon-192x192.png',
                'badge' => '/icon-192x192.png',
                'url' => $orderUrl,
                'data' => [
                    'order_uuid' => $order->uuid,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ],
            ]);

            $vapidPublicKey = env('VAPID_PUBLIC_KEY');
            $vapidPrivateKey = env('VAPID_PRIVATE_KEY');
            $vapidSubject = env('VAPID_SUBJECT', 'mailto:notificacoes@vestezap.com.br');

            if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
                Log::warning('VAPID keys não configuradas. Push notifications não serão enviadas.');
                return;
            }

            // Check if web-push package is available
            if (!class_exists(\Minishlink\WebPush\WebPush::class)) {
                Log::warning('Biblioteca web-push não instalada. Execute: composer require minishlink/web-push');
                return;
            }

            $auth = [
                'VAPID' => [
                    'subject' => $vapidSubject,
                    'publicKey' => $vapidPublicKey,
                    'privateKey' => $vapidPrivateKey,
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            foreach ($subscriptions as $subscription) {
                try {
                    $webPushSubscription = \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'keys' => [
                            'p256dh' => $subscription->public_key,
                            'auth' => $subscription->auth_token,
                        ],
                    ]);

                    $webPush->queueNotification($webPushSubscription, $payload);
                } catch (\Exception $e) {
                    Log::error('Erro ao criar subscription para push do cliente: ' . $e->getMessage());
                }
            }

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    Log::error('Erro ao enviar push notification para cliente: ' . $report->getReason());
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar push notification para cliente: ' . $e->getMessage());
        }
    }
}

