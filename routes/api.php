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
use App\Http\Controllers\ReturnController;

/*
|--------------------------------------------------------------------------
| TEST AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/test-auth', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'token' => $request->bearerToken(),
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);

/*
|--------------------------------------------------------------------------
| MIDTRANS CALLBACK (PUBLIC - WAJIB PUBLIC)
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback', [MidtransController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER
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

        Route::post('/sales', [SaleController::class, 'store']);
        Route::get('/sales', [SaleController::class, 'mySales']);
        Route::get('/sales/summary', [SaleController::class, 'mySalesSummary']);
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | MIDTRANS (LOGIN REQUIRED)
    |--------------------------------------------------------------------------
    */
    Route::prefix('midtrans')->group(function () {
        Route::post('/charge', [MidtransController::class, 'create']);
        Route::post('/create', [MidtransController::class, 'createTransaction']);
    });

    /*
    |--------------------------------------------------------------------------
    | RETURNS (LOGIN REQUIRED)
    |--------------------------------------------------------------------------
    */
    Route::prefix('returns')->group(function () {

        // Kasir ajukan return
        Route::post('/', [ReturnController::class, 'store']);

        // Lihat semua return
        Route::get('/', [ReturnController::class, 'index']);

        // Detail return
        Route::get('/{id}', [ReturnController::class, 'show']);

        // Admin approve return
        Route::post('/{id}/approve', [ReturnController::class, 'approve']);

        // Admin reject return
        Route::post('/{id}/reject', [ReturnController::class, 'reject']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
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
        | REPORTS
        |--------------------------------------------------------------------------
        */
        Route::middleware('role.access:REPORTS')
            ->prefix('reports')
            ->group(function () {

                Route::get('/summary', [ReportController::class, 'summary']);
                Route::get('/transactions', [ReportController::class, 'transactions']);
                Route::get('/transactions/{sale}', [ReportController::class, 'transactionDetail']);
                Route::get('/cashier', [ReportController::class, 'reportByCashier']);
                Route::get('/stock', [ReportController::class, 'stock']);
                Route::get('/chart/profit', [ReportController::class, 'profitChart']);

                Route::get(
                    '/transactions/export/csv',
                    [ReportController::class, 'exportTransactionsCsv']
                );

                Route::get(
                    '/transactions/export/pdf',
                    [ReportController::class, 'exportTransactionsPdf']
                );

                Route::get(
                    '/transactions/{sale}/export/pdf',
                    [ReportController::class, 'exportTransactionDetailPdf']
                );
            });
    });

    /*
    |--------------------------------------------------------------------------
    | WAREHOUSE
    |--------------------------------------------------------------------------
    */
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
