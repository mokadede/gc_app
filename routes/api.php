<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    // Services (all staff can read)
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Orders (all staff can CRUD)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePayment']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // === Owner Only ===
    // Services CRUD
    Route::post('/services', [ServiceController::class, 'store'])->middleware('can:owner-only');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->middleware('can:owner-only');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->middleware('can:owner-only');

    // Employees
    Route::apiResource('/employees', EmployeeController::class)->middleware('can:owner-only');

    // Vouchers
    Route::apiResource('/vouchers', VoucherController::class)->middleware('can:owner-only');

    // Reports
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);
});
