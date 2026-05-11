<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;

Route::post('/auth/login', [AuthController::class, 'login']);

// TES: Endpoint publik tanpa auth — untuk verifikasi server membaca file terbaru
Route::get('/test-server', function () {
    return response()->json(['status' => 'OK', 'message' => 'Server file terbaru aktif', 'time' => now()->toDateTimeString()]);
});

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

    // Services CRUD (Buka akses untuk sementara demi testing)
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

    // Employees
    Route::apiResource('/employees', EmployeeController::class)->middleware('can:owner-only');

    // Vouchers (Buka akses untuk sementara demi testing)
    Route::apiResource('/vouchers', VoucherController::class);

    // Reports
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);
});
