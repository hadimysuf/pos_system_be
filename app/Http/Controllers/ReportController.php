<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * 📊 Sales Report
     * GET /api/admin/reports/sales
     */
    public function salesReport()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $query = Sale::query();

        if ($start && $end) {
            $query->whereBetween('sale_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
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
     * 💰 Profit Report (per hari)
     */
    public function profitReport()
    {
        $start = request('start_date') ?? now()->subDays(7);
        $end   = request('end_date') ?? now();

        $data = Sale::select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(profit) as profit')
        )
            ->whereBetween('sale_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * 📦 Stock Report
     */
    public function stockReport()
    {
        $products = Product::select(
            'id',
            'name',
            'stock',
            'low_stock_threshold'
        )
            ->orderBy('stock')
            ->get();

        return response()->json($products);
    }

    /**
     * 📊 Sales Transaction Report (Detail)
     */
    public function salesTransactions()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $query = Sale::with(['items.product'])
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('sale_date', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            });

        $sales = $query->latest()->paginate(10);

        return response()->json([
            'summary' => [
                'total_transactions' => $query->count(),
                'total_amount'       => $query->sum('total_amount'),
                'total_cost'         => $query->sum('total_cost'),
                'total_profit'       => $query->sum('profit'),
            ],
            'data' => $sales
        ]);
    }
    public function transactionDetail(Request $request)
    {
        $from = $request->query('from', Carbon::today()->toDateString());
        $to   = $request->query('to', Carbon::today()->toDateString());

        $sales = Sale::with(['user', 'items.product'])
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ])
            ->get();

        $transactions = [];
        $totalAmount = 0;
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($sales as $sale) {

            $items = [];
            $saleCost = 0;

            foreach ($sale->items as $item) {
                $subtotal = $item->qty * $item->price;
                $cost = $item->qty * $item->product->cost;
                $profit = $subtotal - $cost;

                $saleCost += $cost;

                $items[] = [
                    'product'  => $item->product->name,
                    'qty'      => $item->qty,
                    'price'    => $item->price,
                    'cost'     => $item->product->cost,
                    'subtotal' => $subtotal,
                    'profit'   => $profit,
                ];
            }

            $saleProfit = $sale->total - $saleCost;

            $transactions[] = [
                'invoice' => $sale->invoice_number,
                'date'    => $sale->created_at->format('Y-m-d H:i:s'),
                'cashier' => $sale->user->name ?? '-',
                'total'   => $sale->total,
                'cost'    => $saleCost,
                'profit'  => $saleProfit,
                'items'   => $items,
            ];

            $totalAmount += $sale->total;
            $totalCost += $saleCost;
            $totalProfit += $saleProfit;
        }

        return response()->json([
            'summary' => [
                'from' => $from,
                'to' => $to,
                'total_transactions' => count($transactions),
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
            ],
            'transactions' => $transactions
        ]);
    }
    public function salesSummary()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $query = Sale::query();

        if ($start && $end) {
            $query->whereBetween('sale_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
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
     * 📄 DETAIL TRANSAKSI
     */
    public function salesDetail()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $sales = Sale::with(['items.product'])
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('sale_date', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay(),
                ]);
            })
            ->orderBy('sale_date', 'desc')
            ->get();

        return response()->json($sales);
    }
}
