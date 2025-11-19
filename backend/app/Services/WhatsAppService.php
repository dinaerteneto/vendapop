<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;

class WhatsAppService
{
    public function generateOrderMessage(Tenant $tenant, Order $order, Customer $customer, array $items): string
    {
        $message = "Olá, gostaria de confirmar meu pedido nº *{$order->order_number}* na loja *{$tenant->name}*:\n\n";
        $message .= "*Itens:*\n";

        foreach ($items as $item) {
            $productName = $item['product']->name;
            $size = $item['size'] ? "Tam: {$item['size']}" : "";
            $color = $item['color'] ? "Cor: {$item['color']}" : "";
            $details = implode(' - ', array_filter([$size, $color]));
            $quantity = $item['quantity'];

            $message .= "- {$productName} ({$details}) x{$quantity}\n";
        }

        $totalFormatted = number_format($order->total_amount, 2, ',', '.');
        $message .= "\n*Total: R$ {$totalFormatted}*\n\n";

        $message .= "*Meus dados:*\n";
        $message .= "Nome: {$customer->name}\n";
        if ($customer->email) $message .= "E-mail: {$customer->email}\n";
        if ($customer->phone) $message .= "Celular: {$customer->phone}\n";

        return $message;
    }

    public function generateWhatsAppUrl(Tenant $tenant, string $message): string
    {
        $phone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number);
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}
