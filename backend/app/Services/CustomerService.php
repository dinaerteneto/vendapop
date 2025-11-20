<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tenant;

class CustomerService
{
    public function findOrCreate(Tenant $tenant, array $customerData): Customer
    {
        $email = $customerData['email'] ?? null;
        $phone = $customerData['phone'] ?? null;

        $query = Customer::where('tenant_id', $tenant->id);

        $query->where(function ($q) use ($email, $phone) {
            if ($email) {
                $q->orWhere('email', $email);
            }
            if ($phone) {
                $q->orWhere('phone', $phone);
            }
        });

        $customer = $query->first();

        if ($customer) {
            // Update existing customer info
            $customer->update([
                'name' => $customerData['name'],
                'email' => $email ?: $customer->email,
                'phone' => $phone ?: $customer->phone,
            ]);
            return $customer;
        }

        return Customer::create([
            'tenant_id' => $tenant->id,
            'name' => $customerData['name'],
            'email' => $email,
            'phone' => $phone,
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
