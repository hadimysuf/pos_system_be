<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * =========================
     * ADMIN - LIST ALL SALES
     * =========================
     */
    public function index(Request $request)
    {
        $query = Sale::with(['items', 'cashier']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        return SaleResource::collection(
            $query->latest()->paginate(10)
        );
    }

    /**
     * =========================
     * KASIR - CREATE TRANSACTION
     * =========================
     */
    public function store(SaleRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $totalAmount = 0;
            $totalCost   = 0;

            $sale = Sale::create([
                'invoice_number' => 'INV-' . now()->format('YmdHis'),
                'user_id'        => $request->user()->id,
                'sale_date'      => now(),
                'payment_method' => 'cash',
            ]);

            foreach ($request->items as $item) {

                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = $item['quantity'];

                if ($product->stock < $qty) {
                    abort(400, 'Stock not enough for ' . $product->name);
                }

                $subtotal     = $product->price * $qty;
                $costSubtotal = $product->cost * $qty;
                $profit       = $subtotal - $costSubtotal;

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $product->price,
                    'cost'       => $product->cost,
                    'subtotal'   => $subtotal,
                    'profit'     => $profit,
                ]);

                $product->decrement('stock', $qty);

                $totalAmount += $subtotal;
                $totalCost   += $costSubtotal;
            }

            $sale->update([
                'total_amount' => $totalAmount,
                'total_cost'   => $totalCost,
                'profit'       => $totalAmount - $totalCost,
            ]);

            return new SaleResource($sale->load('items'));
        });
    }

    /**
     * =========================
     * ADMIN - DASHBOARD SUMMARY
     * =========================
     */
    public function summary(Request $request)
    {
        $query = Sale::query();

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        } else {
            $query->whereDate('sale_date', today());
        }

        return response()->json([
            'total_transactions' => $query->count(),
            'total_amount'       => $query->sum('total_amount'),
            'total_cost'         => $query->sum('total_cost'),
            'total_profit'       => $query->sum('profit'),
        ]);
    }

    
    // public function mySales(Request $request)
    // {
    //     $user = $request->user();

    //     $query = Sale::with('items')
    //         ->where('user_id', $user->id);

    //     if ($request->start_date && $request->end_date) {
    //         $query->whereBetween('sale_date', [
    //             $request->start_date,
    //             $request->end_date
    //         ]);
    //     } else {
    //         $query->whereDate('sale_date', today());
    //     }

    //     return SaleResource::collection(
    //         $query->latest()->paginate(10)
    //     );
    // }

    /**
     * =========================
     * KASIR - MY SUMMARY (TEXT INFO)
     * =========================
     */
    public function mySummary(Request $request)
    {
        $user = $request->user();

        $query = Sale::where('user_id', $user->id);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        } else {
            $query->whereDate('sale_date', today());
        }

        return response()->json([
            'my_transactions' => $query->count(),
            'my_total_sales'  => $query->sum('total_amount'),
            'my_profit'       => $query->sum('profit'),
        ]);
    }

    /**
     * =========================
     * KASIR - MY TRANSACTION HISTORY
     * =========================
     */

    public function mySalesSummary(Request $request)
    {
        $user = $request->user();

        $query = Sale::where('user_id', $user->id);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        } else {
            $query->whereDate('sale_date', today());
        }

        return response()->json([
            'cashier'            => $user->name,
            'total_transactions' => $query->count(),
            'total_amount'       => $query->sum('total_amount'),
            'total_cost'         => $query->sum('total_cost'),
            'total_profit'       => $query->sum('profit'),
        ]);
    }

    //admin audit transaksi per kasir

    public function adminSales(Request $request)
    {
        $query = Sale::with(['cashier', 'items.product']);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        return SaleResource::collection(
            $query->latest()->paginate(10)
        );
    }


    public function show(Request $request, Sale $sale)
    {
        // Cegah kasir lihat transaksi kasir lain
        if ($sale->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized access to this transaction'
            ], 403);
        }

        return new SaleResource(
            $sale->load(['items.product', 'cashier'])
        );
    }
}
