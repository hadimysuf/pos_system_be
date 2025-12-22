<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * List sales + filter tanggal
     */
    public function index()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $query = Sale::query();

        if ($start && $end) {
            $query->whereBetween('sale_date', [$start, $end]);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        return SaleResource::collection(
            $query->latest()->paginate(10)
        );
    }

    /**
     * Create new sale
     */
    public function store(SaleRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $totalAmount = 0;
            $totalCost   = 0;

            $sale = Sale::create([
                'invoice_number' => 'INV-' . now()->format('YmdHis'),
                'user_id' => $request->user_id ?? 1,
                'sale_date' => now(),
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
     * Sales summary (dashboard)
     */
    public function summary()
    {
        $start = request('start_date');
        $end   = request('end_date');

        $query = Sale::query();

        if ($start && $end) {
            $query->whereBetween('sale_date', [$start, $end]);
        } else {
            $query->whereDate('sale_date', today());
        }

        return response()->json([
            'total_sales' => $query->sum('total_price'),
            'profit' => $query->sum('profit'),
            'total_transactions' => $query->count(),
        ]);
    }
}
