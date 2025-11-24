<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\UseCases\Admin\GetCategoriesUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private GetCategoriesUseCase $getCategoriesUseCase;
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        GetCategoriesUseCase $getCategoriesUseCase,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->getCategoriesUseCase = $getCategoriesUseCase;
        $this->categoryRepository = $categoryRepository;
    }

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validar colunas permitidas para ordenação
        $allowedSorts = ['id', 'name', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        return $this->getCategoriesUseCase->execute($tenant, $perPage, $sortBy, $sortDirection);
    }

    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Slug será gerado automaticamente pela biblioteca sluggable
        $validated['tenant_id'] = $tenant->id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $validated['image_url'] = url(Storage::url($path));
        }

        $category = $this->categoryRepository->create($validated);

        return response()->json($category, 201);
    }

    public function show(Request $request, Category $category)
    {
        $tenant = $request->user()->tenant;

        // Ensure category belongs to tenant
        $category = $this->categoryRepository->findByIdAndTenant($category->id, $tenant->id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $tenant = $request->user()->tenant;

        // Ensure category belongs to tenant
        $category = $this->categoryRepository->findByIdAndTenant($category->id, $tenant->id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Slug será atualizado automaticamente pela biblioteca sluggable se o nome mudar

        if ($request->hasFile('image')) {
            // Delete old image if exists and is local
            if ($category->image_url) {
                 $oldPath = str_replace(url('/storage/'), '', $category->image_url);
                 if (Storage::disk('public')->exists($oldPath)) {
                     Storage::disk('public')->delete($oldPath);
                 }
            }

            $path = $request->file('image')->store('categories', 'public');
            $validated['image_url'] = url(Storage::url($path));
        }

        $this->categoryRepository->update($category, $validated);

        return response()->json($category->fresh());
    }

    public function destroy(Request $request, Category $category)
    {
        $tenant = $request->user()->tenant;

        // Category is already resolved by route model binding using slug
        // Ensure category belongs to tenant
        if ($category->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($category->products()->exists()) {
            return response()->json(['message' => 'Não é possível excluir uma categoria que possui produtos.'], 400);
        }

        if ($category->image_url) {
             $oldPath = str_replace(url('/storage/'), '', $category->image_url);
             if (Storage::disk('public')->exists($oldPath)) {
                 Storage::disk('public')->delete($oldPath);
             }
        }

        $this->categoryRepository->delete($category);
        return response()->noContent();
    }
}
