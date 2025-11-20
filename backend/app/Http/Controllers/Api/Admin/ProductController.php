<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\UseCases\Admin\GetProductsUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private GetProductsUseCase $getProductsUseCase;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        GetProductsUseCase $getProductsUseCase,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getProductsUseCase = $getProductsUseCase;
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validar colunas permitidas para ordenação
        $allowedSorts = ['id', 'name', 'price', 'created_at', 'updated_at', 'category'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        return $this->getProductsUseCase->execute($tenant, $perPage, $sortBy, $sortDirection);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'promotional_price' => 'nullable|numeric',
            'category_id' => 'nullable',
            'sizes' => 'required|array',
            'colors' => 'nullable|array',
            'description' => 'nullable|string',
            'main_image_url' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array', // Gallery URLs
            'images.*' => 'string',
            'is_active' => 'boolean',
            'is_hot' => 'boolean',
        ]);

        $tenant = $request->user()->tenant;

        // Validate category belongs to tenant if provided
        if (!empty($validated['category_id'])) {
            $category = \App\Models\Category::where('id', $validated['category_id'])
                ->where('tenant_id', $tenant->id)
                ->first();
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Remove campos que não existem mais na tabela products
        $productData = collect($validated)->except(['main_image_url', 'image', 'images'])->toArray();
        $productData['tenant_id'] = $tenant->id;

        $product = $this->productRepository->create($productData);

        // 1. Handle Main Image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->images()->create([
                'url' => url(Storage::url($path)),
                'path' => $path,
                'is_external' => false,
                'is_main' => true,
            ]);
        } elseif (!empty($validated['main_image_url'])) {
            $product->images()->create([
                'url' => $validated['main_image_url'],
                'path' => null,
                'is_external' => true, // Assume external if passed as string URL
                'is_main' => true,
            ]);
        }

        // 2. Handle Gallery Images (URLs only for now via this endpoint)
        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $galleryUrl) {
                $product->images()->create([
                    'url' => $galleryUrl,
                    'path' => null,
                    'is_external' => true,
                    'is_main' => false,
                ]);
            }
        }

        return response()->json($product->load('images'), 201);
    }

    public function show(Request $request, Product $product)
    {
        $tenant = $request->user()->tenant;

        // Ensure product belongs to tenant
        $product = $this->productRepository->findByIdAndTenant($product->id, $tenant->id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return $product->load(['category', 'images']);
    }

    public function update(Request $request, Product $product)
    {
        $tenant = $request->user()->tenant;

        // Ensure product belongs to tenant
        $product = $this->productRepository->findByIdAndTenant($product->id, $tenant->id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string',
            'price' => 'numeric',
            'promotional_price' => 'nullable|numeric',
            'category_id' => 'nullable',
            'sizes' => 'array',
            'colors' => 'nullable|array',
            'description' => 'nullable|string',
            'main_image_url' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array', // Gallery
            'images.*' => 'string',
            'is_active' => 'boolean',
            'is_hot' => 'boolean',
        ]);

        // Validate category belongs to tenant if provided
        if (!empty($validated['category_id'])) {
            $category = \App\Models\Category::where('id', $validated['category_id'])
                ->where('tenant_id', $tenant->id)
                ->first();
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Update basic product info
        $productData = collect($validated)->except(['main_image_url', 'image', 'images'])->toArray();
        $this->productRepository->update($product, $productData);

        // 1. Handle Main Image Update
        if ($request->hasFile('image')) {
            // Delete old main image
            $oldMain = $product->images()->where('is_main', true)->first();
            if ($oldMain) {
                if (!$oldMain->is_external && $oldMain->path) {
                    Storage::disk('public')->delete($oldMain->path);
                }
                $oldMain->delete();
            }

            // Upload new
            $path = $request->file('image')->store('products', 'public');
            $product->images()->create([
                'url' => url(Storage::url($path)),
                'path' => $path,
                'is_external' => false,
                'is_main' => true,
            ]);
        } elseif (array_key_exists('main_image_url', $validated)) {
            // If main_image_url is explicitly sent (even if empty/null)
            $newUrl = $validated['main_image_url'];
            $currentMain = $product->images()->where('is_main', true)->first();

            if ($newUrl) {
                // If URL changed or didn't exist
                if (!$currentMain || $currentMain->url !== $newUrl) {
                     if ($currentMain) {
                         if (!$currentMain->is_external && $currentMain->path) {
                             Storage::disk('public')->delete($currentMain->path);
                         }
                         $currentMain->delete();
                     }
                     $product->images()->create([
                         'url' => $newUrl,
                         'is_external' => true,
                         'is_main' => true,
                     ]);
                }
            } else {
                // If sent as null/empty, remove main image
                if ($currentMain) {
                     if (!$currentMain->is_external && $currentMain->path) {
                         Storage::disk('public')->delete($currentMain->path);
                     }
                     $currentMain->delete();
                }
            }
        }

        // 2. Handle Gallery Sync
        // Note: This implementation assumes 'images' contains the FULL list of desired gallery URLs.
        if (array_key_exists('images', $validated)) {
            $incomingUrls = $validated['images'] ?? [];

            // Get current gallery images
            $currentGallery = $product->images()->where('is_main', false)->get();

            // Delete removed
            foreach ($currentGallery as $img) {
                if (!in_array($img->url, $incomingUrls)) {
                     if (!$img->is_external && $img->path) {
                         Storage::disk('public')->delete($img->path);
                     }
                     $img->delete();
                }
            }

            // Add new
            $currentUrls = $currentGallery->pluck('url')->toArray();
            foreach ($incomingUrls as $url) {
                if (!in_array($url, $currentUrls)) {
                    $product->images()->create([
                        'url' => $url,
                        'is_external' => true,
                        'is_main' => false,
                    ]);
                }
            }
        }

        return response()->json($product->load('images'));
    }

    public function destroy(Request $request, Product $product)
    {
        $tenant = $request->user()->tenant;

        // Ensure product belongs to tenant
        $product = $this->productRepository->findByIdAndTenant($product->id, $tenant->id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Delete physical files
        foreach ($product->images as $image) {
            if (!$image->is_external && $image->path) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $this->productRepository->delete($product);
        return response()->noContent();
    }
}
