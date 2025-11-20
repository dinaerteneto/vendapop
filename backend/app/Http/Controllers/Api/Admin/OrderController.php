<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validar colunas permitidas para ordenação
        $allowedSorts = ['id', 'order_number', 'status', 'total_amount', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query = Order::with('customer');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage);
    }

    public function show(Order $order)
    {
        return $order->load(['customer', 'items.product.images']);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:NEW,DONE,CANCELED',
        ]);

        $order->update($validated);

        return response()->json($order->load(['customer', 'items.product.images']));
    }
}

