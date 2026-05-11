<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    
    // Admin only services routes
    Route::post('/services', [ServiceController::class, 'store'])->middleware('can:manage-services');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->middleware('can:manage-services');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->middleware('can:manage-services');

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePayment']);

    // Reports
    Route::get('/reports/daily', [ReportController::class, 'daily'])->middleware('can:view-reports');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->middleware('can:view-reports');
});
