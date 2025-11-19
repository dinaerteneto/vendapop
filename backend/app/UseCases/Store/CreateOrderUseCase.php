<?php

namespace App\UseCases\Store;

use App\Models\Tenant;
use App\Services\CustomerService;
use App\Services\OrderService;

class CreateOrderUseCase
{
    private OrderService $orderService;
    private CustomerService $customerService;

    public function __construct(OrderService $orderService, CustomerService $customerService)
    {
        $this->orderService = $orderService;
        $this->customerService = $customerService;
    }

    public function execute(Tenant $tenant, array $customerData, array $items, ?string $notes = null): array
    {
        // Validate customer data
        $this->customerService->validateCustomerData($customerData);

        // Find or create customer
        $customer = $this->customerService->findOrCreate($tenant, $customerData);

        // Create order
        $order = $this->orderService->createOrder($tenant, $customer, $items, $notes);

        // Generate WhatsApp link
        $whatsAppLink = $this->orderService->generateWhatsAppLink($tenant, $order, $customer);

        return [
            'order' => $order,
            'customer' => $customer,
            'whatsapp_link' => $whatsAppLink,
        ];
    }
}
