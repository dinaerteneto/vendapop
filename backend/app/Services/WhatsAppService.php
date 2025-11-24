<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;

class WhatsAppService
{
    public function generateOrderMessage(Tenant $tenant, Order $order, Customer $customer, array $items): string
    {
        // Use FRONTEND_URL to build the tracking link (fixed format)
        $frontendUrl = rtrim(config('services.frontend_url', 'http://localhost:5173'), '/');
        $trackingUrl = "{$frontendUrl}/{$tenant->slug}/order/{$order->uuid}";

        $message = "Olá, fiz um pedido na loja *{$tenant->name}*.\n";
        $message .= "Pedido nº: *{$order->order_number}*\n\n";

        // Add order notes if available
        if ($order->notes) {
            $message .= "*Observações:*\n{$order->notes}\n\n";
        }

        $message .= "Veja os detalhes do pedido no link abaixo:\n";
        $message .= $trackingUrl;

        return $message;
    }

    public function generateWhatsAppUrl(Tenant $tenant, string $message): string
    {
        $phone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number);

        // Add country code 55 (Brazil) if not present
        if (!empty($phone) && substr($phone, 0, 2) !== '55') {
            $phone = '55' . $phone;
        }

        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}
