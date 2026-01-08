<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleAccessMenuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WarehouseController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', fn(Request $request) => $request->user());

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD (ALL LOGGED USER)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/chart/sales', [DashboardController::class, 'salesChart']);
        Route::get('/chart/profit', [DashboardController::class, 'profitChart']);
        Route::get('/chart/top-products', [DashboardController::class, 'topProducts']);
        Route::get('/low-stock', [DashboardController::class, 'lowStock']);
    });

    /*
    |--------------------------------------------------------------------------
    | CASHIER
    |--------------------------------------------------------------------------
    */
    Route::prefix('cashier')->group(function () {

        // Buat transaksi
        Route::post('/sales', [SaleController::class, 'store']);

        // Riwayat transaksi kasir sendiri
        Route::get('/sales', [SaleController::class, 'mySales']);

        // Ringkasan transaksi kasir
        Route::get('/sales/summary', [SaleController::class, 'mySalesSummary']);

        // Detail transaksi kasir
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN (ROLE BASED ACCESS)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        Route::middleware('role.access:USER_MANAGE')
            ->apiResource('users', UserController::class);

        Route::middleware('role.access:ROLE_MANAGE')
            ->apiResource('roles', RoleController::class);

        Route::middleware('role.access:MENU_MANAGE')
            ->apiResource('menus', MenuController::class);

        Route::middleware('role.access:ROLE_ACCESS')->group(function () {
            Route::get('role-access', [RoleAccessMenuController::class, 'index']);
            Route::post('role-access', [RoleAccessMenuController::class, 'store']);
        });




        /*
        |--------------------------------------------------------------------------
        | REPORTS (ADMIN ONLY)
        |--------------------------------------------------------------------------
        */
        Route::middleware('role.access:REPORTS')
            ->prefix('reports')
            ->group(function () {

                // Ringkasan global
                Route::get('/summary', [ReportController::class, 'summary']);

                // Semua transaksi (filterable)
                Route::get('/transactions', [ReportController::class, 'transactions']);

                // Detail 1 transaksi
                Route::get('/transactions/{sale}', [ReportController::class, 'transactionDetail']);

                // Audit per kasir
                Route::get('/cashier', [ReportController::class, 'reportByCashier']);

                // Laporan stok
                Route::get('/stock', [ReportController::class, 'stock']);

                // Grafik profit
                Route::get('/chart/profit', [ReportController::class, 'profitChart']);
            });
    });

    Route::prefix('warehouse')->middleware('auth:sanctum')->group(function () {

        Route::get('/stocks', [WarehouseController::class, 'index']);
        Route::get('/low-stock', [WarehouseController::class, 'lowStock']);

        Route::post('/stock-in', [WarehouseController::class, 'stockIn']);
        Route::post('/stock-out', [WarehouseController::class, 'stockOut']);

        Route::get('/logs', [WarehouseController::class, 'logs']);

        Route::get('/restock-recommendation', [WarehouseController::class, 'restockRecommendation']);
    });

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout']);
});
