<?php

namespace App\Http\Controllers;

use App\Models\ReturnTransaction;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    public function index()
    {
        $returns = ReturnTransaction::with([
            'sale',
            'items.saleItem.product'
        ])->latest()->get();

        return response()->json([
            'data' => $returns
        ]);
    }
    // public function show($id)
    // {
    //     $sale = Sale::with('items.product')
    //         ->findOrFail($id);

    //     return response()->json([
    //         'data' => $sale
    //     ]);
    // }
    /*
    |--------------------------------------------------------------------------
    | STORE (Kasir Ajukan Return)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {

            $sale = Sale::with('items')->findOrFail($request->sale_id);

            $return = ReturnTransaction::create([
                'sale_id' => $sale->id,
                'user_id' => Auth::id(),
                'reason' => $request->reason,
                'status' => 'pending',
                'total_refund' => 0
            ]);

            $totalRefund = 0;

            foreach ($request->items as $item) {

                $saleItem = SaleItem::findOrFail($item['sale_item_id']);

                if ($item['quantity'] > $saleItem->quantity) {
                    throw new \Exception("Quantity return melebihi pembelian.");
                }

                $subtotal = $saleItem->price * $item['quantity'];
                $cost = $saleItem->cost * $item['quantity'];
                $profit = $saleItem->profit * ($item['quantity'] / $saleItem->quantity);

                ReturnItem::create([
                    'return_id' => $return->id,
                    'sale_item_id' => $saleItem->id,
                    'quantity' => $item['quantity'],
                    'price' => $saleItem->price,
                    'cost' => $saleItem->cost,
                    'subtotal' => $subtotal,
                    'profit' => $profit,
                ]);

                $totalRefund += $subtotal;
            }

            $return->update(['total_refund' => $totalRefund]);

            DB::commit();

            return response()->json([
                'message' => 'Return berhasil diajukan',
                'data' => $return
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE (Admin Approve Return)
    |--------------------------------------------------------------------------
    */
    public function approve($id)
    {
        DB::beginTransaction();

        try {

            $return = ReturnTransaction::with('items.saleItem.product', 'sale')
                ->findOrFail($id);

            if ($return->status !== 'pending') {
                throw new \Exception("Return sudah diproses.");
            }

            $sale = $return->sale;

            foreach ($return->items as $item) {

                $product = $item->saleItem->product;

                // Tambah stok  
                $product->increment('stock', $item->quantity);

                // Stock log
                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => Auth::id(),
                    'type' => 'RETURN',
                    'change' => $item->quantity,
                    'note' => 'Return dari invoice ' . $sale->invoice_number
                ]);

                // Kurangi nilai sale
                $sale->total_amount -= $item->subtotal;
                $sale->total_cost -= ($item->cost * $item->quantity);
                $sale->profit -= $item->profit;
            }

            $sale->save();

            $return->update([
                'status' => 'approved',
                'approved_at' => Carbon::now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Return berhasil di-approve'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */
    public function reject($id)
    {
        $return = ReturnTransaction::findOrFail($id);

        if ($return->status !== 'pending') {
            return response()->json(['error' => 'Return sudah diproses'], 400);
        }

        $return->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Return ditolak'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL RETURN
    |--------------------------------------------------------------------------
    */
    public function detail($id)
    {
        $return = ReturnTransaction::with([
            'sale',
            'items.saleItem.product'
        ])->findOrFail($id);

        return response()->json([
            'data' => $return
        ]);
    }
}
