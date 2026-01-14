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
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\MidtransController;

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

        Route::middleware('role.access:SUPPLIER_MANAGE')
            ->apiResource('suppliers', SupplierController::class);




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

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('warehouse')
            ->middleware('role.access:WAREHOUSE_ACCESS')
            ->group(function () {

                Route::get('/', [WarehouseController::class, 'index']);

                Route::middleware('role.access:STOCK_IN')
                    ->post('/stock-in', [WarehouseController::class, 'stockIn']);

                Route::middleware('role.access:STOCK_OUT')
                    ->post('/stock-out', [WarehouseController::class, 'stockOut']);

                Route::middleware('role.access:STOCK_LOGS')
                    ->get('/logs', [WarehouseController::class, 'logs']);

                Route::middleware('role.access:RESTOCK_RECOMMENDATION')
                    ->get('/restock-recommendation', [WarehouseController::class, 'restockRecommendation']);

                Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])
                    ->middleware('role.access:PURCHASE_ORDERS');

                Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])
                    ->middleware('role.access:PURCHASE_ORDERS');

                Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])
                    ->middleware('role.access:PURCHASE_ORDERS');

                Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
                    ->middleware('role.access:PURCHASE_ORDERS');

                Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
                    ->middleware('role.access:PURCHASE_ORDERS');

                Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])
                    ->middleware('role.access:PURCHASE_ORDERS');
            });
    });
    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| PUBLIC (MIDTRANS CALLBACK)
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback', [MidtransController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| MIDTRANS (HANYA UNTUK USER LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/midtrans/charge', [MidtransController::class, 'create']);
    Route::post('/midtrans/create', [MidtransController::class, 'createTransaction']);
});
