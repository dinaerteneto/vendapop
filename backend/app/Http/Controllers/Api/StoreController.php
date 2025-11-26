<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\RotatingBannerRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\UseCases\Store\CreateOrderUseCase;
use App\UseCases\Store\GetOrderUseCase;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    private TenantRepositoryInterface $tenantRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private ProductRepositoryInterface $productRepository;
    private RotatingBannerRepositoryInterface $bannerRepository;
    private CreateOrderUseCase $createOrderUseCase;
    private GetOrderUseCase $getOrderUseCase;

    public function __construct(
        TenantRepositoryInterface $tenantRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        RotatingBannerRepositoryInterface $bannerRepository,
        CreateOrderUseCase $createOrderUseCase,
        GetOrderUseCase $getOrderUseCase
    ) {
        $this->tenantRepository = $tenantRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->bannerRepository = $bannerRepository;
        $this->createOrderUseCase = $createOrderUseCase;
        $this->getOrderUseCase = $getOrderUseCase;
    }

    public function storeInfo(Request $request, $storeSlug)
    {
        $tenant = $this->tenantRepository->findBySlug($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $tenant->load('socials');
        return response()->json($tenant);
    }

    public function banners(Request $request, $storeSlug)
    {
        $tenant = $this->tenantRepository->findBySlug($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $banners = $this->bannerRepository->findActiveByTenant($tenant->id);
        return response()->json($banners);
    }

    public function categories(Request $request, $storeSlug)
    {
        $tenant = $this->tenantRepository->findBySlug($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $categories = $this->categoryRepository->findByTenant($tenant->id);
        return response()->json($categories);
    }

    public function products(Request $request, $storeSlug)
    {
        $tenant = $this->tenantRepository->findBySlug($storeSlug);

        if (!$tenant) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $search = $request->get('search');
        $categoryId = $request->get('category_id');

        // Buscar produtos diretamente do repositório
        if ($search) {
            $products = $this->productRepository->searchByTenant($tenant->id, $search);
        } elseif ($categoryId) {
            $products = $this->productRepository->filterByCategory($tenant->id, $categoryId);
        } else {
            $products = $this->productRepository->findActiveByTenant($tenant->id);
        }

        // Carregar variações antes de transformar
        $products->load('variations');

        return \App\Http\Resources\ProductResource::collection($products);
    }

    // ... rest of the controller (productDetail, checkout) remain the same
    // Need to make sure they are included here or I will overwrite them?
    // Since write tool overwrites the file, I MUST include the full content.
    // Let me copy the rest of the methods from previous context.

    public function productDetail(Request $request, $storeSlug, \App\Models\Product $product)
    {
        // Product is already resolved by route model binding using slug
        // The resolveRouteBinding method already filters by tenant_id and is_active
        // So if we got here, the product exists, belongs to the tenant and is active

        // Load relationships if needed
        $product->load(['category', 'images', 'variations']);

        // Retornar Resource - Laravel retorna { data: {...} } para recursos únicos
        // Precisamos acessar response()->json() para retornar diretamente
        $resource = new \App\Http\Resources\ProductResource($product);
        return response()->json($resource->toArray($request));
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
        $tenant = $this->tenantRepository->findBySlug($storeSlug);
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
        $tenant = $this->tenantRepository->findBySlug($storeSlug);
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
        $tenant = $this->tenantRepository->findBySlug($storeSlug);
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
