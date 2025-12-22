<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('sales', SaleController::class);
Route::post('/sales', [SaleController::class, 'store']);

Route::prefix('dashboard')->group(function () {
    Route::get('/summary', [DashboardController::class, 'summary']);
    Route::get('/chart/sales', [DashboardController::class, 'salesChart']);
    Route::get('/chart/profit', [DashboardController::class, 'profitChart']);
    Route::get('/chart/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/low-stock', [DashboardController::class, 'lowStock']);
});

// Route::get('dashboard/summary', [DashboardController::class, 'summary']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('products/low-stock', [ProductController::class, 'lowStock']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
