<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tenant;

class CustomerService
{
    public function findOrCreate(Tenant $tenant, array $customerData): Customer
    {
        // Try to find existing customer by email if provided
        if (!empty($customerData['email'])) {
            $customer = Customer::where('tenant_id', $tenant->id)
                               ->where('email', $customerData['email'])
                               ->first();

            if ($customer) {
                return $customer;
            }
        }

        // Create new customer
        return Customer::create([
            'tenant_id' => $tenant->id,
            'name' => $customerData['name'],
            'email' => $customerData['email'] ?? null,
            'phone' => $customerData['phone'] ?? null,
        ]);
    }

    public function validateCustomerData(array $customerData): void
    {
        if (empty($customerData['name'])) {
            throw new \Exception('Customer name is required');
        }

        if (empty($customerData['email']) && empty($customerData['phone'])) {
            throw new \Exception('Either email or phone must be provided');
        }
    }
}
