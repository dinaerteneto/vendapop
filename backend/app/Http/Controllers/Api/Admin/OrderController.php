<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\NotificationService;
use App\UseCases\Admin\GetOrdersUseCase;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private GetOrdersUseCase $getOrdersUseCase;
    private OrderRepositoryInterface $orderRepository;
    private NotificationService $notificationService;

    public function __construct(
        GetOrdersUseCase $getOrdersUseCase,
        OrderRepositoryInterface $orderRepository,
        NotificationService $notificationService
    ) {
        $this->getOrdersUseCase = $getOrdersUseCase;
        $this->orderRepository = $orderRepository;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $status = $request->get('status');

        // Validar colunas permitidas para ordenação
        $allowedSorts = ['id', 'order_number', 'status', 'total_amount', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        return $this->getOrdersUseCase->execute($tenant, $perPage, $sortBy, $sortDirection, $status);
    }

    public function show(Request $request, Order $order)
    {
        $tenant = $request->user()->tenant;

        // Order is already resolved by route model binding using UUID
        // Ensure order belongs to tenant
        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return $order->load(['customer', 'items.product.images']);
    }

    public function update(Request $request, Order $order)
    {
        $tenant = $request->user()->tenant;

        // Order is already resolved by route model binding using UUID
        // Ensure order belongs to tenant
        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', OrderStatus::values())],
        ]);

        $oldStatus = $order->status;
        $this->orderRepository->update($order, $validated);

        $order = $order->fresh()->load(['customer', 'items.product.images']);

        // Notify customer if status changed to SENT or DONE
        if ($oldStatus !== $order->status && in_array($order->status, ['SENT', 'DONE'])) {
            $this->notificationService->notifyCustomerOrderStatus($order);
        }

        return response()->json($order);
    }
}

