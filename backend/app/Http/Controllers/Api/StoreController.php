<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{

    public function storeInfo(Request $request)
    {
        // Retorna dados da loja + redes sociais
        $tenant = $this->tenantService->getTenant();
        $tenant->load('socials'); // Eager load socials
        return response()->json($tenant);
    }

    public function products(Request $request)
    {
        // TenantScope is applied automatically via Trait
        $query = Product::where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $term = $request->search;
            $query->where('name', 'like', "%{$term}%");
        }

        return response()->json($query->with('category')->get());
    }

    // ... rest of the controller (productDetail, checkout) remain the same
    // Need to make sure they are included here or I will overwrite them?
    // Since write tool overwrites the file, I MUST include the full content.
    // Let me copy the rest of the methods from previous context.

    public function productDetail($storeSlug, $productId)
    {
        $product = Product::where('is_active', true)->findOrFail($productId);
        return response()->json($product);
    }

    public function checkout(Request $request, $storeSlug)
    {
        $validated = $request->validate([
            'customer.name' => 'required|string',
            'customer.email' => 'nullable|email',
            'customer.phone' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $customerData = $request->input('customer');
        if (empty($customerData['email']) && empty($customerData['phone'])) {
            return response()->json([
                'message' => 'É necessário informar E-mail ou Celular.',
                'errors' => ['customer' => ['Informe e-mail ou celular.']]
            ], 422);
        }

        // Buscar tenant diretamente do slug (middleware já validou, mas vamos garantir)
        $tenant = \App\Models\Tenant::where('slug', $storeSlug)->first();
        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        try {
            return DB::transaction(function () use ($request, $customerData, $tenant) {
                $customer = null;
                if (!empty($customerData['email'])) {
                    $customer = Customer::where('email', $customerData['email'])->first();
                }

                if (!$customer) {
                    $customer = Customer::create([
                        'tenant_id' => $tenant->id, // Definir explicitamente o tenant_id
                        'name' => $customerData['name'],
                        'email' => $customerData['email'] ?? null,
                        'phone' => $customerData['phone'] ?? null,
                    ]);
                }

                $totalAmount = 0;
                $orderItemsData = [];

                foreach ($request->input('items') as $item) {
                    // Buscar produto sem o Global Scope, verificando tenant_id explicitamente
                    $product = Product::where('id', $item['product_id'])
                                    ->where('tenant_id', $tenant->id)
                                    ->where('is_active', true)
                                    ->firstOrFail();

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

                $count = Order::count() + 1;
                $orderNumber = sprintf('PED-%s-%06d', date('Y'), $count);

                $order = Order::create([
                    'tenant_id' => $tenant->id, // Definir explicitamente o tenant_id
                    'customer_id' => $customer->id,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount,
                    'status' => 'novo',
                ]);

                if ($request->has('notes')) {
                    $order->notes = $request->input('notes');
                    $order->save();
                }

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

                $whatsappLink = $this->generateWhatsappLink($tenant, $order, $customer, $orderItemsData);

                return response()->json([
                    'message' => 'Order created successfully',
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'whatsapp_link' => $whatsappLink,
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing order', 'error' => $e->getMessage()], 500);
        }
    }

    private function generateWhatsappLink($tenant, $order, $customer, $items)
    {
        $msg = "Olá, gostaria de confirmar meu pedido nº *{$order->order_number}* na loja *{$tenant->name}*:\n\n";
        $msg .= "*Itens:*\n";

        foreach ($items as $item) {
            $pName = $item['product']->name;
            $size = $item['size'] ? "Tam: {$item['size']}" : "";
            $color = $item['color'] ? "Cor: {$item['color']}" : "";
            $qty = $item['quantity'];
            $details = implode(' - ', array_filter([$size, $color]));

            $msg .= "- {$pName} ({$details}) x{$qty}\n";
        }

        $totalFormatted = number_format($order->total_amount, 2, ',', '.');
        $msg .= "\n*Total: R$ {$totalFormatted}*\n\n";

        $msg .= "*Meus dados:*\n";
        $msg .= "Nome: {$customer->name}\n";
        if ($customer->email) $msg .= "E-mail: {$customer->email}\n";
        if ($customer->phone) $msg .= "Celular: {$customer->phone}\n";

        $phone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number);
        $encodedMsg = urlencode($msg);

        return "https://wa.me/{$phone}?text={$encodedMsg}";
    }
}
