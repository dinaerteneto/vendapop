<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function destroy(ProductImage $productImage)
    {
        // Delete physical file if it's local
        if (!$productImage->is_external && $productImage->path) {
            Storage::disk('public')->delete($productImage->path);
        }

        $productImage->delete();

        return response()->json(['message' => 'Imagem removida com sucesso.']);
    }

    public function setAsMain(Request $request, ProductImage $productImage)
    {
        $product = $productImage->product;

        // Remove main flag from all images of this product
        $product->images()->update(['is_main' => false]);

        // Set this image as main
        $productImage->update(['is_main' => true]);

        return response()->json(['message' => 'Imagem principal atualizada com sucesso.', 'image' => $productImage->fresh()]);
    }
}

