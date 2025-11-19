<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\StoreSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas Públicas da Loja (Tenant)
Route::middleware(['tenant'])->prefix('{storeSlug}')->group(function () {
    Route::get('/', [StoreController::class, 'storeInfo']); // Nova rota para info da loja
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/products', [StoreController::class, 'products']);
    Route::get('/products/{product}', [StoreController::class, 'productDetail']);
    Route::post('/checkout', [StoreController::class, 'checkout']);
});

// Rotas Admin
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Rotas protegidas do Admin
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('categories', AdminCategoryController::class);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);

        Route::get('/store', [StoreSettingsController::class, 'show']);
        Route::put('/store', [StoreSettingsController::class, 'update']);
    });
});
