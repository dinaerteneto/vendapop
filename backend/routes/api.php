<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\EmailVerificationController;
use App\Http\Controllers\Api\Admin\GoogleAuthController;
use App\Http\Controllers\Api\Admin\OTPAuthController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PasswordResetController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ProductImageController;
use App\Http\Controllers\Api\Admin\PushSubscriptionController;
use App\Http\Controllers\Api\Admin\RegistrationController;
use App\Http\Controllers\Api\Admin\RotatingBannerController;
use App\Http\Controllers\Api\Admin\StoreSettingsController;
use App\Http\Controllers\Api\Admin\ProductAttributeController;
use App\Http\Controllers\Api\CustomerPushSubscriptionController;
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
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('/verify-email', [EmailVerificationController::class, 'verify']);
    Route::post('/resend-verification', [EmailVerificationController::class, 'resend']);

    // Google OAuth
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
    Route::post('/auth/google/link', [GoogleAuthController::class, 'link']);
    Route::post('/onboarding', [GoogleAuthController::class, 'onboarding']);

    // OTP + Magic Link
    Route::post('/otp/send', [OTPAuthController::class, 'send']);
    Route::post('/otp/verify', [OTPAuthController::class, 'verify']);
    Route::get('/magic-login', [OTPAuthController::class, 'magicLogin']);

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

        Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store']);
        Route::delete('/push-subscriptions/{id}', [PushSubscriptionController::class, 'destroy']);

        Route::apiResource('banners', RotatingBannerController::class);
        Route::post('/banners/update-order', [RotatingBannerController::class, 'updateOrder']);

        // Product Attributes
        Route::apiResource('product-attributes', ProductAttributeController::class);
    });
});

// Rotas Públicas da Loja (Tenant)
Route::middleware(['tenant'])->prefix('{storeSlug}')->group(function () {
    Route::get('/manifest.json', [ManifestController::class, 'show']); // Dynamic PWA Manifest
    Route::get('/', [StoreController::class, 'storeInfo']); // Nova rota para info da loja
    Route::get('/banners', [StoreController::class, 'banners']); // Banners rotativos ativos
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/products', [StoreController::class, 'products']);
    Route::get('/products/{product}', [StoreController::class, 'productDetail']);
    Route::post('/checkout', [StoreController::class, 'checkout']);
    Route::get('/order/{uuid}', [StoreController::class, 'getOrder']);
    Route::get('/order/{uuid}/whatsapp', [StoreController::class, 'getWhatsAppLink']);
    Route::post('/order/{orderUuid}/push-subscriptions', [CustomerPushSubscriptionController::class, 'store']);
    Route::delete('/order/{orderUuid}/push-subscriptions/{id}', [CustomerPushSubscriptionController::class, 'destroy']);
});
