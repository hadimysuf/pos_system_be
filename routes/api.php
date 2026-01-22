<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    UserController,
    RoleController,
    MenuController,
    RoleAccessMenuController,
    DashboardController,
    ProductController,
    CategoryController,
    SaleController,
    ReportController,
    WarehouseController,
    SupplierController,
    PurchaseOrderController,
    MidtransController
};

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
Route::post('/midtrans/callback', [MidtransController::class, 'callback']);

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

        Route::middleware('role.access:SUPPLIER_MANAGE')->group(function () {
            Route::apiResource('suppliers', SupplierController::class);
            Route::put('suppliers/{supplier}/activate', [SupplierController::class, 'activate']);
        });

        Route::middleware('role.access:REPORTS')
            ->prefix('reports')
            ->group(function () {
                Route::get('/summary', [ReportController::class, 'summary']);
                Route::get('/transactions', [ReportController::class, 'transactions']);
                Route::get('/transactions/{sale}', [ReportController::class, 'transactionDetail']);
                Route::get('/cashier', [ReportController::class, 'reportByCashier']);
                Route::get('/stock', [ReportController::class, 'stock']);
                Route::get('/chart/profit', [ReportController::class, 'profitChart']);
                Route::get('/transactions/export/csv', [ReportController::class, 'exportTransactionsCsv']);
                Route::get('/transactions/export/pdf', [ReportController::class, 'exportTransactionsPdf']);
                Route::get('/transactions/{sale}/export/pdf', [ReportController::class, 'exportTransactionDetailPdf']);
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

            Route::post('/stock-in', [WarehouseController::class, 'stockIn'])
                ->middleware('role.access:STOCK_IN');

            Route::post('/stock-out', [WarehouseController::class, 'stockOut'])
                ->middleware('role.access:STOCK_OUT');

            Route::get('/logs', [WarehouseController::class, 'logs'])
                ->middleware('role.access:STOCK_LOGS');

            Route::get('/restock-recommendation', [WarehouseController::class, 'restockRecommendation'])
                ->middleware('role.access:RESTOCK_RECOMMENDATION');

            Route::apiResource('purchase-orders', PurchaseOrderController::class)
                ->middleware('role.access:PURCHASE_ORDERS');

            Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
            Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        });

    /*
    |--------------------------------------------------------------------------
    | MIDTRANS (LOGIN)
    |--------------------------------------------------------------------------
    */
    Route::post('/midtrans/create', [MidtransController::class, 'create']);
    Route::post('/midtrans/finalize/{orderId}', [MidtransController::class, 'finalize']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
