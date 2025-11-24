<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderConfirmationMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tenant;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ProductRepositoryInterface $productRepository;
    private WhatsAppService $whatsAppService;
    private NotificationService $notificationService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        WhatsAppService $whatsAppService,
        NotificationService $notificationService
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->whatsAppService = $whatsAppService;
        $this->notificationService = $notificationService;
    }

    public function createOrder(Tenant $tenant, Customer $customer, array $items, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($tenant, $customer, $items, $notes) {
            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($items as $item) {
                $product = $this->productRepository->findByIdAndTenant($item['product_id'], $tenant->id);

                if (!$product) {
                    throw new \Exception("Product with ID {$item['product_id']} not found");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItemsData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'subtotal' => $subtotal,
                ];
            }

            $orderNumber = $this->orderRepository->generateOrderNumber($tenant->id);

            $order = $this->orderRepository->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::NEW->value,
                'notes' => $notes,
            ]);

            // Create order items
            foreach ($orderItemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $data['product']->id,
                    'product_name' => $data['product']->name,
                    'unit_price' => $data['product']->price,
                    'quantity' => $data['quantity'],
                    'size' => $data['size'],
                    'color' => $data['color'],
                    'subtotal' => $data['subtotal'],
                ]);
            }

            // Notify administrators about the new order
            $this->notificationService->notifyNewOrder($order);

            // Send confirmation email to customer if they have an email
            if ($customer->email) {
                $this->sendCustomerConfirmationEmail($order);
            }

            return $order;
        });
    }

    public function generateWhatsAppLink(Tenant $tenant, Order $order, Customer $customer): string
    {
        $order->load('items.product');
        $items = $order->items->map(function ($item) {
            return [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
            ];
        })->toArray();

        $message = $this->whatsAppService->generateOrderMessage($tenant, $order, $customer, $items);
        return $this->whatsAppService->generateWhatsAppUrl($tenant, $message);
    }

    /**
     * Send confirmation email to customer
     */
    private function sendCustomerConfirmationEmail(Order $order): void
    {
        try {
            $order->load(['customer', 'tenant', 'items']);
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
            $orderUrl = "{$frontendUrl}/{$order->tenant->slug}/order/{$order->uuid}";

            Mail::to($order->customer->email)->send(new OrderConfirmationMail($order, $orderUrl));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de confirmação para o cliente: ' . $e->getMessage());
        }
    }
}
