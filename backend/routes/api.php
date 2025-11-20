<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ProductImageController;
use App\Http\Controllers\Api\Admin\RegistrationController;
use App\Http\Controllers\Api\Admin\StoreSettingsController;
use App\Http\Controllers\Api\ManifestController;
use App\Http\Controllers\Api\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas Admin
Route::prefix('admin')->group(function () {
    Route::post('/register', [RegistrationController::class, 'store']); // Nova rota de registro
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Rotas protegidas do Admin
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('products', AdminProductController::class);
        Route::delete('product-images/{productImage}', [ProductImageController::class, 'destroy']);
        Route::put('product-images/{productImage}/set-as-main', [ProductImageController::class, 'setAsMain']);
        Route::apiResource('categories', AdminCategoryController::class);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::put('/orders/{order}', [AdminOrderController::class, 'update']);

        Route::get('/customers', [AdminCustomerController::class, 'index']);
        Route::get('/customers/{customer}', [AdminCustomerController::class, 'show']);
        Route::put('/customers/{customer}', [AdminCustomerController::class, 'update']);

        Route::get('/store', [StoreSettingsController::class, 'show']);
        Route::put('/store', [StoreSettingsController::class, 'update']);
        Route::post('/store', [StoreSettingsController::class, 'update']); // POST for file uploads

        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });
});

// Rotas Públicas da Loja (Tenant)
Route::middleware(['tenant'])->prefix('{storeSlug}')->group(function () {
    Route::get('/manifest.json', [ManifestController::class, 'show']); // Dynamic PWA Manifest
    Route::get('/', [StoreController::class, 'storeInfo']); // Nova rota para info da loja
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/products', [StoreController::class, 'products']);
    Route::get('/products/{product}', [StoreController::class, 'productDetail']);
    Route::post('/checkout', [StoreController::class, 'checkout']);
    Route::get('/order/{uuid}', [StoreController::class, 'getOrder']);
});
