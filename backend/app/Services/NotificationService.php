<?php

namespace App\Services;

use App\Mail\NewOrderNotificationMail;
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
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
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

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
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
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
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
}

