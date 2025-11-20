<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;

class WhatsAppService
{
    public function generateOrderMessage(Tenant $tenant, Order $order, Customer $customer, array $items): string
    {
        // Use the tenant's configured store_url to build the tracking link
        $baseUrl = rtrim($tenant->store_url, '/');
        $trackingUrl = "{$baseUrl}/{$tenant->slug}/order/{$order->uuid}";

        $message = "Olá, fiz um pedido na loja *{$tenant->name}*.\n";
        $message .= "Pedido nº: *{$order->order_number}*\n\n";
        $message .= "Veja os detalhes do pedido no link abaixo:\n";
        $message .= $trackingUrl;

        return $message;
    }

    public function generateWhatsAppUrl(Tenant $tenant, string $message): string
    {
        $phone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number);
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}
