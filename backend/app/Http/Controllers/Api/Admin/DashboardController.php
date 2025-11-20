<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        // Vendas hoje (pedidos com status DONE criados hoje)
        $salesToday = Order::where('tenant_id', $tenant->id)
            ->where('status', 'DONE')
            ->whereDate('created_at', today())
            ->sum('total_amount');

        // Pedidos novos (status NEW)
        $newOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', 'NEW')
            ->count();

        // Produtos ativos
        $activeProducts = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->count();

        // Total de pedidos hoje (independente do status)
        $ordersToday = Order::where('tenant_id', $tenant->id)
            ->whereDate('created_at', today())
            ->count();

        // Total de clientes
        $totalCustomers = Customer::where('tenant_id', $tenant->id)->count();

        // Vendas do mês atual
        $salesThisMonth = Order::where('tenant_id', $tenant->id)
            ->where('status', 'DONE')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        return response()->json([
            'sales_today' => number_format($salesToday, 2, '.', ''),
            'new_orders' => $newOrders,
            'active_products' => $activeProducts,
            'orders_today' => $ordersToday,
            'total_customers' => $totalCustomers,
            'sales_this_month' => number_format($salesThisMonth, 2, '.', ''),
        ]);
    }
}

