<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * =========================
     * GLOBAL SUMMARY
     * =========================
     */
    public function summary(Request $request)
    {
        $query = Sale::query();

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        return response()->json([
            'total_transactions' => $query->count(),
            'total_amount'       => $query->sum('total_amount'),
            'total_cost'         => $query->sum('total_cost'),
            'total_profit'       => $query->sum('profit'),
        ]);
    }

    /**
     * =========================
     * TRANSACTION LIST
     * =========================
     */
    public function transactions(Request $request)
    {
        $query = Sale::with(['cashier', 'items.product']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return $query->latest()->paginate(10);
    }

    /**
     * =========================
     * DETAIL 1 TRANSACTION
     * =========================
     */
    public function transactionDetail(Sale $sale)
    {
        return response()->json(
            $sale->load(['cashier', 'items.product'])
        );
    }

    /**
     * =========================
     * AUDIT PER CASHIER
     * =========================
     */
    public function reportByCashier(Request $request)
    {
        return Sale::select(
            'user_id',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(total_amount) as total_amount'),
            DB::raw('SUM(profit) as total_profit')
        )
            ->with('cashier:id,name')
            ->groupBy('user_id')
            ->get();
    }

    /**
     * =========================
     * STOCK REPORT
     * =========================
     */
    public function stock()
    {
        return Product::select(
            'id',
            'name',
            'stock',
            'low_stock_threshold'
        )->orderBy('stock')->get();
    }

    /**
     * =========================
     * PROFIT CHART
     * =========================
     */
    public function profitChart(Request $request)
    {
        $start = $request->start_date ?? now()->subDays(7);
        $end   = $request->end_date ?? now();

        return Sale::select(
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
    }
}
