<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/superadmin')->group(function () {
    // Public routes
    Route::post('/login', [\App\Http\Controllers\Api\SuperAdmin\AuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth:sanctum', 'check.superadmin'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\SuperAdmin\AuthController::class, 'logout']);

        Route::get('/tenants', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class, 'index']);
        Route::get('/tenants/{id}', [\App\Http\Controllers\Api\SuperAdmin\TenantController::class, 'show']);

        Route::get('/waitlist', [\App\Http\Controllers\Api\SuperAdmin\WaitlistController::class, 'index']);
        Route::put('/waitlist/{id}', [\App\Http\Controllers\Api\SuperAdmin\WaitlistController::class, 'update']);
        Route::post('/waitlist/batch', [\App\Http\Controllers\Api\SuperAdmin\WaitlistController::class, 'batchApprove']);

        Route::get('/feedbacks', [\App\Http\Controllers\Api\SuperAdmin\FeedbackController::class, 'index']);
        Route::get('/feedbacks/{id}', [\App\Http\Controllers\Api\SuperAdmin\FeedbackController::class, 'show']);
        Route::put('/feedbacks/{id}', [\App\Http\Controllers\Api\SuperAdmin\FeedbackController::class, 'update']);

        Route::get('/invites', [\App\Http\Controllers\Api\SuperAdmin\InviteController::class, 'index']);
        Route::post('/invites', [\App\Http\Controllers\Api\SuperAdmin\InviteController::class, 'store']);
        Route::put('/invites/{id}/toggle', [\App\Http\Controllers\Api\SuperAdmin\InviteController::class, 'toggle']);

        // Spots
        Route::get('/spots', [\App\Http\Controllers\Api\SuperAdmin\SpotController::class, 'index']);
        Route::get('/spots/stats', [\App\Http\Controllers\Api\SuperAdmin\SpotController::class, 'stats']);
        Route::post('/spots', [\App\Http\Controllers\Api\SuperAdmin\SpotController::class, 'store']);
        Route::put('/spots/{id}/replenish', [\App\Http\Controllers\Api\SuperAdmin\SpotController::class, 'replenish']);
        Route::delete('/spots/{id}', [\App\Http\Controllers\Api\SuperAdmin\SpotController::class, 'destroy']);
    });
});
