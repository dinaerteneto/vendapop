<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
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

        return Category::orderBy($sortBy, $sortDirection)->paginate($perPage);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $validated['image_url'] = url(Storage::url($path));
        }

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'string',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

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

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Não é possível excluir uma categoria que possui produtos.'], 400);
        }

        if ($category->image_url) {
             $oldPath = str_replace(url('/storage/'), '', $category->image_url);
             if (Storage::disk('public')->exists($oldPath)) {
                 Storage::disk('public')->delete($oldPath);
             }
        }

        $category->delete();
        return response()->noContent();
    }
}
