<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Services\ProductAttributeService;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    private ProductAttributeService $attributeService;

    public function __construct(ProductAttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * Lista todos os atributos do tenant
     */
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        $attributes = ProductAttribute::where('tenant_id', $tenant->id)
            ->orderBy('order')
            ->get();

        return response()->json($attributes);
    }

    /**
     * Cria um novo atributo
     */
    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $attribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'order' => $validated['order'] ?? 0,
            'is_active' => true,
        ]);

        return response()->json($attribute, 201);
    }

    /**
     * Atualiza um atributo
     */
    public function update(Request $request, string $id)
    {
        $tenant = $request->user()->tenant;

        $attribute = ProductAttribute::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $attribute->update($validated);

        return response()->json($attribute);
    }

    /**
     * Remove um atributo (apenas se não estiver em uso)
     */
    public function destroy(Request $request, string $id)
    {
        $tenant = $request->user()->tenant;

        $attribute = ProductAttribute::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($this->attributeService->isAttributeInUse($attribute)) {
            return response()->json([
                'message' => 'Não é possível remover este atributo pois está em uso em produtos.'
            ], 422);
        }

        $attribute->delete();

        return response()->json(['message' => 'Atributo removido com sucesso'], 200);
    }

}

