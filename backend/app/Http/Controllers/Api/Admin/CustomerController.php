<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\UseCases\Admin\GetCustomersUseCase;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private GetCustomersUseCase $getCustomersUseCase;
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(
        GetCustomersUseCase $getCustomersUseCase,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->getCustomersUseCase = $getCustomersUseCase;
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validar colunas permitidas para ordenação
        $allowedSorts = ['id', 'name', 'email', 'phone', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        return $this->getCustomersUseCase->execute($tenant, $perPage, $sortBy, $sortDirection);
    }

    public function show(Request $request, Customer $customer)
    {
        $tenant = $request->user()->tenant;

        // Ensure customer belongs to tenant
        $customer = $this->customerRepository->findByIdAndTenant($customer->id, $tenant->id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return $customer->load('orders');
    }

    public function update(Request $request, Customer $customer)
    {
        $tenant = $request->user()->tenant;

        // Ensure customer belongs to tenant
        $customer = $this->customerRepository->findByIdAndTenant($customer->id, $tenant->id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',
        ]);

        $this->customerRepository->update($customer, $validated);

        return response()->json($customer->fresh()->loadCount('orders'));
    }
}

