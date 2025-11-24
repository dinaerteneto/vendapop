<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RotatingBanner;
use App\UseCases\Admin\CreateBannerUseCase;
use App\UseCases\Admin\DeleteBannerUseCase;
use App\UseCases\Admin\GetBannersUseCase;
use App\UseCases\Admin\UpdateBannerOrderUseCase;
use App\UseCases\Admin\UpdateBannerUseCase;
use Illuminate\Http\Request;

class RotatingBannerController extends Controller
{
    private GetBannersUseCase $getBannersUseCase;
    private CreateBannerUseCase $createBannerUseCase;
    private UpdateBannerUseCase $updateBannerUseCase;
    private DeleteBannerUseCase $deleteBannerUseCase;
    private UpdateBannerOrderUseCase $updateBannerOrderUseCase;

    public function __construct(
        GetBannersUseCase $getBannersUseCase,
        CreateBannerUseCase $createBannerUseCase,
        UpdateBannerUseCase $updateBannerUseCase,
        DeleteBannerUseCase $deleteBannerUseCase,
        UpdateBannerOrderUseCase $updateBannerOrderUseCase
    ) {
        $this->getBannersUseCase = $getBannersUseCase;
        $this->createBannerUseCase = $createBannerUseCase;
        $this->updateBannerUseCase = $updateBannerUseCase;
        $this->deleteBannerUseCase = $deleteBannerUseCase;
        $this->updateBannerOrderUseCase = $updateBannerOrderUseCase;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        $banners = $this->getBannersUseCase->execute($tenant);
        return response()->json($banners);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'nullable|image|max:5120',
            'image_url' => 'nullable|url',
            'link_url' => 'nullable|url',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Validar que pelo menos uma imagem foi fornecida
        if (!$request->hasFile('image') && empty($validated['image_url'])) {
            return response()->json([
                'message' => 'É necessário fornecer uma imagem (upload ou URL)',
                'errors' => ['image' => ['Forneça uma imagem via upload ou URL']]
            ], 422);
        }

        $tenant = $request->user()->tenant;

        $banner = $this->createBannerUseCase->execute(
            $tenant,
            $request->file('image'),
            $validated['image_url'] ?? null,
            $validated['link_url'] ?? null,
            $validated['title'] ?? null,
            $validated['description'] ?? null,
            $validated['order'] ?? null,
            $validated['is_active'] ?? true
        );

        return response()->json($banner, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $tenant = $request->user()->tenant;
        $banner = RotatingBanner::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($banner);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tenant = $request->user()->tenant;
        $banner = RotatingBanner::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'image' => 'nullable|image|max:5120',
            'image_url' => 'nullable|url',
            'link_url' => 'nullable|url',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $banner = $this->updateBannerUseCase->execute(
            $banner,
            $request->file('image'),
            $validated['image_url'] ?? null,
            $validated['link_url'] ?? null,
            $validated['title'] ?? null,
            $validated['description'] ?? null,
            $validated['order'] ?? null,
            $validated['is_active'] ?? null
        );

        return response()->json($banner);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $tenant = $request->user()->tenant;
        $banner = RotatingBanner::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();

        $this->deleteBannerUseCase->execute($banner);

        return response()->json(['message' => 'Banner removido com sucesso'], 200);
    }

    /**
     * Update order of banners
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:rotating_banners,id',
            'banners.*.order' => 'required|integer|min:0',
        ]);

        $tenant = $request->user()->tenant;
        $this->updateBannerOrderUseCase->execute($tenant, $validated['banners']);

        return response()->json(['message' => 'Ordem atualizada com sucesso'], 200);
    }
}
