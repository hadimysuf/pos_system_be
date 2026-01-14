<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * =========================
     * BASE TRANSACTION QUERY
     * =========================
     */
    private function baseTransactionQuery(Request $request)
    {
        $query = Sale::with(['cashier', 'items.product']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        if ($request->cashier_id) {
            $query->where('user_id', $request->cashier_id);
        }

        return $query;
    }

    /**
     * =========================
     * TRANSACTION LIST
     * =========================
     */
    public function transactions(Request $request)
    {
        $baseQuery = Sale::query();

        if ($request->start_date && $request->end_date) {
            $baseQuery->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        if ($request->cashier_id) {
            $baseQuery->where('user_id', $request->cashier_id);
        }

        // 👉 SUMMARY (TIDAK PAGINATE)
        $summary = [
            'total_transactions' => (clone $baseQuery)->count(),
            'total_revenue'      => (clone $baseQuery)->sum('total_amount'),
        ];

        // 👉 TABLE (PAGINATE)
        $paginated = (clone $baseQuery)
            ->with('cashier')
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'summary' => $summary,
        ]);
    }


    /**
     * =========================
     * EXPORT EXCEL
     * =========================
     */
    public function exportTransactionsCsv(Request $request)
    {
        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM (BIAR EXCEL WINDOWS AMAN)
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // HEADER LAPORAN
            fputcsv($handle, ['LAPORAN TRANSAKSI']);
            fputcsv($handle, [
                'Periode',
                $request->start_date && $request->end_date
                    ? $request->start_date . ' s/d ' . $request->end_date
                    : 'Semua Tanggal'
            ]);
            fputcsv($handle, []);

            // HEADER TABLE
            fputcsv($handle, [
                'ID',
                'Tanggal',
                'Kasir',
                'Total',
                'Metode Pembayaran',
                'Status'
            ]);

            Sale::with('cashier')
                ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay(),
                    ]);
                })
                ->when($request->cashier_id, function ($q) use ($request) {
                    $q->where('user_id', $request->cashier_id);
                })
                ->orderBy('created_at')
                ->chunk(500, function ($sales) use ($handle) {
                    foreach ($sales as $sale) {
                        fputcsv($handle, [
                            $sale->id,
                            $sale->created_at->format('d-m-Y H:i'),
                            $sale->cashier->name ?? '-',
                            $sale->total_amount,
                            $sale->payment_method,
                            $sale->status,
                        ]);
                    }
                });

            // TOTAL
            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL PENDAPATAN',
                '',
                '',
                Sale::when($request->start_date && $request->end_date, function ($q) use ($request) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay(),
                    ]);
                })
                    ->when($request->cashier_id, function ($q) use ($request) {
                        $q->where('user_id', $request->cashier_id);
                    })
                    ->sum('total_amount')
            ]);

            fclose($handle);
        }, 'transactions-report.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }


    /**
     * =========================
     * EXPORT PDF
     * =========================
     */
    public function exportTransactionsPdf(Request $request)
    {
        $sales = $this->baseTransactionQuery($request)
            ->with('cashier')
            ->latest()
            ->get();

        $totalRevenue = $sales->sum('total_amount');

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.transactions-pdf',
            [
                'sales' => $sales,
                'totalRevenue' => $totalRevenue,
                'filters' => $request->only([
                    'start_date',
                    'end_date',
                    'cashier_id'
                ]),
            ]
        )->download('transactions-report.pdf');
    }




    /**
     * =========================
     * DETAIL TRANSACTION
     * =========================
     */
    public function transactionDetail(Sale $sale)
    {
        return response()->json(
            $sale->load(['cashier', 'items.product'])
        );
    }
    public function exportTransactionDetailPdf(Sale $sale)
    {
        $sale->load(['cashier', 'items.product']);

        return Pdf::loadView(
            'reports.transaction-detail-pdf',
            [
                'transaction' => $sale,
            ]
        )->download("transaction-{$sale->id}.pdf");
    }

    /**
     * =========================
     * REPORT BY CASHIER
     * =========================
     */
    public function reportByCashier()
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
            'category_id',
            'low_stock_threshold'
        )
            ->with('category:id,name')
            ->orderBy('stock')
            ->get();
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
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(profit) as profit')
        )
            ->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
