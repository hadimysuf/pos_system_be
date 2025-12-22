<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SaleItem;

class DashboardController extends Controller
{
    /**
     * Dashboard utama (summary + low stock)
     */
    public function index()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $sales = Sale::query();

        if ($start && $end) {
            $sales->whereBetween('sale_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ]);
        } else {
            $sales->whereDate('sale_date', Carbon::today());
        }

        return response()->json([
            'summary' => [
                'total_transactions' => $sales->count(),
                'total_amount'       => $sales->sum('total_amount'),
                'total_cost'         => $sales->sum('total_cost'),
                'total_profit'       => $sales->sum('profit'),
            ],

            'low_stock_products' => Product::whereColumn(
                'stock',
                '<=',
                'low_stock_threshold'
            )
                ->where('is_active', true)
                ->orderBy('stock')
                ->get(['id', 'name', 'stock', 'low_stock_threshold']),
        ]);
    }

    /* ===========================
       📈 SALES CHART (per day)
    =========================== */
    public function salesChart()
    {
        $start = request('start_date') ?? now()->subDays(7);
        $end   = request('end_date') ?? now();

        $data = Sale::select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->whereBetween('sale_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /* ===========================
       💰 PROFIT CHART
    =========================== */
    public function profitChart()
    {
        $start = request('start_date') ?? now()->subDays(7);
        $end   = request('end_date') ?? now();

        $data = Sale::select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(profit) as profit')
        )
            ->whereBetween('sale_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /* ===========================
       🏆 TOP PRODUCTS
    =========================== */
    public function topProducts()
    {
        $data = SaleItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_sold')
        )
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    /* ===========================
       🚨 LOW STOCK
    =========================== */
    public function lowStock()
    {
        $products = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get(['id', 'name', 'stock', 'low_stock_threshold']);

        return response()->json([
            'total' => $products->count(),
            'data'  => $products
        ]);
    }
}
