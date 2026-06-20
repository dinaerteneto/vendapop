<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
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

        // Buscar produtos diretamente do repositório
        $paginator = $this->productRepository->findByTenantWithPagination(
            $tenant->id,
            $perPage,
            $sortBy,
            $sortDirection
        );

        // Carregar variações para todos os produtos antes de transformar
        foreach ($paginator->items() as $product) {
            if (!$product->relationLoaded('variations')) {
                $product->load('variations');
            }
        }

        // Transformar usando Resource (funciona com paginators também)
        return \App\Http\Resources\ProductResource::collection($paginator);
    }

    public function store(Request $request)
    {
        foreach (['attributes', 'variations'] as $field) {
            $value = $request->input($field);
            if (is_string($value)) {
                $request->merge([$field => json_decode($value, true) ?? []]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'promotional_price' => 'nullable|numeric',
            'category_id' => 'nullable',
            'sizes' => 'nullable|array',
            'colors' => 'nullable|array',
            'attributes' => 'nullable|array',
            'attributes.*.attributeId' => 'nullable|integer',
            'attributes.*.attributeName' => 'nullable|string',
            'attributes.*.values' => 'required|array',
            'attributes.*.values.*' => 'string',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|integer',
            'variations.*.attributes' => 'required|array',
            'variations.*.stock' => 'nullable|integer|min:0',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
            'main_image_url' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array', // Gallery URLs
            'images.*' => 'string',
            'is_active' => 'boolean',
            'is_hot' => 'boolean',
            'stock_management_enabled' => 'boolean',
            'action_type' => 'nullable|in:add_to_cart,affiliate_link,whatsapp_contact',
            'affiliate_link' => 'nullable|string',
            'whatsapp_message' => 'nullable|string',
            'button_label' => 'nullable|string',
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

        // Slug será gerado automaticamente pela biblioteca sluggable

        // Remove campos que não existem mais na tabela products
        $productData = collect($validated)->except(['main_image_url', 'image', 'images', 'sizes', 'colors', 'attributes', 'variations'])->toArray();
        $productData['tenant_id'] = $tenant->id;

        $product = $this->productRepository->create($productData);

        // Processar variações ou atributos
        if (!empty($validated['variations']) && is_array($validated['variations'])) {
            // Se variações foram enviadas diretamente, usar elas
            foreach ($validated['variations'] as $variationData) {
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'attributes' => $variationData['attributes'],
                    'stock' => $variationData['stock'] ?? null,
                    'price' => $variationData['price'] ?? null,
                    'sku' => $variationData['sku'] ?? null,
                    'is_active' => $variationData['is_active'] ?? true,
                ]);
            }
        } elseif (!empty($validated['attributes']) && is_array($validated['attributes'])) {
            // Se apenas atributos foram fornecidos, gerar combinações (comportamento antigo)
            $this->processProductAttributes($product, $validated['attributes'], $tenant);
        }

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

        $product->load(['category', 'images', 'variations']);
        return (new \App\Http\Resources\ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Request $request, Product $product)
    {
        $tenant = $request->user()->tenant;

        // Product is already resolved by route model binding using slug
        // Ensure product belongs to tenant
        if ($product->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->load(['category', 'images', 'variations']);
        return new \App\Http\Resources\ProductResource($product);
    }

    public function update(Request $request, Product $product)
    {
        $tenant = $request->user()->tenant;

        // Product is already resolved by route model binding using slug
        // Ensure product belongs to tenant
        if ($product->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        foreach (['attributes', 'variations'] as $field) {
            $value = $request->input($field);
            if (is_string($value)) {
                $request->merge([$field => json_decode($value, true) ?? []]);
            }
        }

        $validated = $request->validate([
            'name' => 'string',
            'price' => 'numeric',
            'promotional_price' => 'nullable|numeric',
            'category_id' => 'nullable',
            'sizes' => 'nullable|array',
            'colors' => 'nullable|array',
            'attributes' => 'nullable|array',
            'attributes.*.attributeId' => 'nullable|integer',
            'attributes.*.attributeName' => 'nullable|string',
            'attributes.*.values' => 'required|array',
            'attributes.*.values.*' => 'string',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|integer',
            'variations.*.attributes' => 'required|array',
            'variations.*.stock' => 'nullable|integer|min:0',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
            'main_image_url' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array', // Gallery
            'images.*' => 'string',
            'is_active' => 'boolean',
            'is_hot' => 'boolean',
            'stock_management_enabled' => 'boolean',
            'action_type' => 'nullable|in:add_to_cart,affiliate_link,whatsapp_contact',
            'affiliate_link' => 'nullable|string',
            'whatsapp_message' => 'nullable|string',
            'button_label' => 'nullable|string',
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

        // Slug será atualizado automaticamente pela biblioteca sluggable se o nome mudar

        // Update basic product info
        $productData = collect($validated)->except(['main_image_url', 'image', 'images', 'sizes', 'colors', 'attributes', 'variations'])->toArray();
        $this->productRepository->update($product, $productData);

        // Processar variações ou atributos
        if (array_key_exists('variations', $validated) && !empty($validated['variations'])) {
            // Se variações foram enviadas diretamente, usar elas
            $product->variations()->delete();

            foreach ($validated['variations'] as $variationData) {
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'attributes' => $variationData['attributes'],
                    'stock' => $variationData['stock'] ?? null,
                    'price' => $variationData['price'] ?? null,
                    'sku' => $variationData['sku'] ?? null,
                    'is_active' => $variationData['is_active'] ?? true,
                ]);
            }
        } elseif (array_key_exists('attributes', $validated)) {
            // Se apenas atributos foram fornecidos, gerar combinações (comportamento antigo)
            $product->variations()->delete();

            if (!empty($validated['attributes']) && is_array($validated['attributes'])) {
                $this->processProductAttributes($product, $validated['attributes'], $tenant);
            }
        }

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

        $product->load(['category', 'images', 'variations']);
        return new \App\Http\Resources\ProductResource($product);
    }

    /**
     * Processa atributos do produto e cria variações simples
     */
    private function processProductAttributes(Product $product, array $attributesData, $tenant): void
    {
        // Primeiro, criar ou garantir que os atributos existam
        $attributesMap = [];

        foreach ($attributesData as $attrData) {
            if (empty($attrData['attributeName']) || empty($attrData['values']) || !is_array($attrData['values'])) {
                continue;
            }

            $attributeName = $attrData['attributeName'];
            $attributeId = $attrData['attributeId'] ?? null;
            $slug = Str::slug($attributeName);

            // Se temos um ID, verificar se existe e pertence ao tenant
            if ($attributeId) {
                $attribute = ProductAttribute::where('id', $attributeId)
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if ($attribute) {
                    // Atualizar nome se mudou
                    if ($attribute->name !== $attributeName) {
                        $attribute->update(['name' => $attributeName]);
                    }
                    $attributesMap[$attributeId] = $attribute;
                } else {
                    // ID fornecido mas não existe, criar novo
                    $attribute = ProductAttribute::create([
                        'tenant_id' => $tenant->id,
                        'name' => $attributeName,
                        'slug' => $slug,
                        'order' => 0,
                        'is_active' => true,
                    ]);
                    $attributesMap[$attribute->id] = $attribute;
                }
            } else {
                // Não temos ID, verificar se existe pelo slug ou criar
                $attribute = ProductAttribute::where('tenant_id', $tenant->id)
                    ->where('slug', $slug)
                    ->first();

                if (!$attribute) {
                    $attribute = ProductAttribute::create([
                        'tenant_id' => $tenant->id,
                        'name' => $attributeName,
                        'slug' => $slug,
                        'order' => 0,
                        'is_active' => true,
                    ]);
                }
                $attributesMap[$attribute->id] = $attribute;
            }

            // Valores são livres, não precisam ser pré-cadastrados
            // Apenas validar que existem valores
            if (empty($attrData['values']) || !is_array($attrData['values'])) {
                continue;
            }
        }

        // Criar variações: gerar todas as combinações possíveis usando IDs
        $variations = $this->generateAttributeCombinations($attributesData, $attributesMap);

        foreach ($variations as $variationAttrs) {
            ProductVariation::create([
                'product_id' => $product->id,
                'attributes' => $variationAttrs,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Gera todas as combinações possíveis de atributos usando IDs
     */
    private function generateAttributeCombinations(array $attributesData, array $attributesMap): array
    {
        if (empty($attributesData)) {
            return [];
        }

        // Mapear attributeId ou buscar pelo nome
        $mappedAttributes = [];
        foreach ($attributesData as $attr) {
            $attributeId = $attr['attributeId'] ?? null;

            if ($attributeId && isset($attributesMap[$attributeId])) {
                $mappedAttributes[] = [
                    'id' => $attributeId,
                    'values' => $attr['values'],
                ];
            } else {
                // Buscar pelo nome/slug
                $slug = Str::slug($attr['attributeName']);
                $found = null;
                foreach ($attributesMap as $id => $attribute) {
                    if ($attribute->slug === $slug) {
                        $found = $id;
                        break;
                    }
                }

                if ($found) {
                    $mappedAttributes[] = [
                        'id' => $found,
                        'values' => $attr['values'],
                    ];
                }
            }
        }

        if (empty($mappedAttributes)) {
            return [];
        }

        // Se tiver apenas um atributo, criar uma variação por valor
        if (count($mappedAttributes) === 1) {
            $attr = $mappedAttributes[0];
            $variations = [];

            foreach ($attr['values'] as $value) {
                $variations[] = [(string)$attr['id'] => $value];
            }

            return $variations;
        }

        // Múltiplos atributos: gerar produto cartesiano usando IDs
        $combinations = [[]];

        foreach ($mappedAttributes as $attr) {
            $attributeId = (string)$attr['id'];
            $newCombinations = [];

            foreach ($combinations as $combination) {
                foreach ($attr['values'] as $value) {
                    $newCombination = $combination;
                    $newCombination[$attributeId] = $value;
                    $newCombinations[] = $newCombination;
                }
            }

            $combinations = $newCombinations;
        }

        return $combinations;
    }

    public function destroy(Request $request, Product $product)
    {
        $tenant = $request->user()->tenant;

        // Product is already resolved by route model binding using slug
        // Ensure product belongs to tenant
        if ($product->tenant_id !== $tenant->id) {
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
