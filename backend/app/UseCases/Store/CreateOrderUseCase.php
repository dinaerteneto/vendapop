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

    public function execute(Tenant $tenant, array $customerData, array $items, ?string $notes = null, bool $generateWhatsAppLink = false): array
    {
        // Validate customer data
        $this->customerService->validateCustomerData($customerData);

        // Find or create customer
        $customer = $this->customerService->findOrCreate($tenant, $customerData);

        // Create order
        $order = $this->orderService->createOrder($tenant, $customer, $items, $notes);

        $result = [
            'order' => $order,
            'customer' => $customer,
        ];

        // Generate WhatsApp link only if requested
        if ($generateWhatsAppLink) {
            $result['whatsapp_link'] = $this->orderService->generateWhatsAppLink($tenant, $order, $customer);
        }

        return $result;
    }
}
