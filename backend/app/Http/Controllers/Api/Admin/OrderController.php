<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer')->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->paginate(20);
    }

    public function show(Order $order)
    {
        return $order->load(['customer', 'items.product']);
    }
}

