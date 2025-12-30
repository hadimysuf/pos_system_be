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
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

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
Route::apiResource('sales', SaleController::class);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', fn(Request $request) => $request->user());

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/chart/sales', [DashboardController::class, 'salesChart']);
        Route::get('/chart/profit', [DashboardController::class, 'profitChart']);
        Route::get('/chart/top-products', [DashboardController::class, 'topProducts']);
        Route::get('/low-stock', [DashboardController::class, 'lowStock']);
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

        Route::middleware('role.access:ROLE_ACCESS')
            ->get('role-access', [RoleAccessMenuController::class, 'index']);

        Route::middleware('role.access:ROLE_ACCESS')
            ->post('role-access', [RoleAccessMenuController::class, 'store']);

        Route::middleware('role.access:REPORTS')->group(function () {
            Route::get('/reports/sales', [ReportController::class, 'salesReport']);
            Route::get('/reports/profit', [ReportController::class, 'profitReport']);
            Route::get('/reports/stock', [ReportController::class, 'stockReport']);
            Route::get('/reports/sales-transactions', [ReportController::class, 'salesTransactions']);
            Route::get('/reports/transaction', [ReportController::class, 'transactionDetail']);
            Route::get('/reports/sales', [ReportController::class, 'salesSummary']);
            Route::get('/reports/sales/detail', [ReportController::class, 'salesDetail']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout']);
});
