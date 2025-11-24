<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UseCases\Store\CreateOrderUseCase;
use App\UseCases\Store\GetBannersUseCase;
use App\UseCases\Store\GetCategoriesUseCase;
use App\UseCases\Store\GetOrderUseCase;
use App\UseCases\Store\GetProductDetailsUseCase;
use App\UseCases\Store\GetProductsUseCase;
use App\UseCases\Store\GetStoreInfoUseCase;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    private GetStoreInfoUseCase $getStoreInfoUseCase;
    private GetCategoriesUseCase $getCategoriesUseCase;
    private GetProductsUseCase $getProductsUseCase;
    private GetProductDetailsUseCase $getProductDetailsUseCase;
    private CreateOrderUseCase $createOrderUseCase;
    private GetOrderUseCase $getOrderUseCase;
    private GetBannersUseCase $getBannersUseCase;

    public function __construct(
        GetStoreInfoUseCase $getStoreInfoUseCase,
        GetCategoriesUseCase $getCategoriesUseCase,
        GetProductsUseCase $getProductsUseCase,
        GetProductDetailsUseCase $getProductDetailsUseCase,
        CreateOrderUseCase $createOrderUseCase,
        GetOrderUseCase $getOrderUseCase,
        GetBannersUseCase $getBannersUseCase
    ) {
        $this->getStoreInfoUseCase = $getStoreInfoUseCase;
        $this->getCategoriesUseCase = $getCategoriesUseCase;
        $this->getProductsUseCase = $getProductsUseCase;
        $this->getProductDetailsUseCase = $getProductDetailsUseCase;
        $this->createOrderUseCase = $createOrderUseCase;
        $this->getOrderUseCase = $getOrderUseCase;
        $this->getBannersUseCase = $getBannersUseCase;
    }

    public function storeInfo(Request $request, $storeSlug)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        return response()->json($tenant);
    }

    public function banners(Request $request, $storeSlug)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $banners = $this->getBannersUseCase->execute($tenant);
        return response()->json($banners);
    }

    public function categories(Request $request, $storeSlug)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $categories = $this->getCategoriesUseCase->execute($tenant);

        return response()->json($categories);
    }

    public function products(Request $request, $storeSlug)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $search = $request->get('search');
        $categoryId = $request->get('category_id');

        $products = $this->getProductsUseCase->execute($tenant, $search, $categoryId);

        return response()->json($products);
    }

    // ... rest of the controller (productDetail, checkout) remain the same
    // Need to make sure they are included here or I will overwrite them?
    // Since write tool overwrites the file, I MUST include the full content.
    // Let me copy the rest of the methods from previous context.

    public function productDetail(Request $request, $storeSlug, \App\Models\Product $product)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        // Product is already resolved by route model binding using slug
        // Ensure product belongs to tenant and is active
        if ($product->tenant_id !== $tenant->id || !$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

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

        // Buscar tenant diretamente do slug
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);
        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        try {
            $result = $this->createOrderUseCase->execute(
                $tenant,
                $customerData,
                $request->input('items'),
                $request->input('notes'),
                false // Don't generate WhatsApp link during checkout
            );

            return response()->json([
                'message' => 'Order created successfully',
                'order_uuid' => $result['order']->uuid,
                'order_number' => $result['order']->order_number,
                'total_amount' => $result['order']->total_amount,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing order', 'error' => $e->getMessage()], 500);
        }
    }

    public function getOrder(Request $request, $storeSlug, $uuid)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);
        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        try {
            $order = $this->getOrderUseCase->execute($tenant, $uuid);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    public function getWhatsAppLink(Request $request, $storeSlug, $uuid)
    {
        $tenant = $this->getStoreInfoUseCase->execute($storeSlug);
        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        try {
            $order = $this->getOrderUseCase->execute($tenant, $uuid);
            $order->load('customer');

            $orderService = app(\App\Services\OrderService::class);
            $whatsAppLink = $orderService->generateWhatsAppLink($tenant, $order, $order->customer);

            return response()->json([
                'whatsapp_link' => $whatsAppLink,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }
}
