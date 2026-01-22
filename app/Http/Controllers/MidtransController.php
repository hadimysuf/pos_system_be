<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use App\Models\Sale;
use App\Services\MidtransService;
use Illuminate\Support\Str;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\SaleItem;

class MidtransController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = false; 
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }


    public function create(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        $orderId = 'INV-' . Str::uuid();
        $user = $request->user();

        $sale = Sale::create([
            'invoice_number'     => $orderId,
            'order_id'           => $orderId,
            'user_id'            => $user->id,
            'sale_date'          => now(),
            'payment_method'     => 'midtrans',
            'transaction_status' => 'pending',
            'items_snapshot'     => json_encode($request->items),
        ]);

        $grossAmount = collect($request->items)->sum(function ($i) {
            $product = Product::find($i['product_id']);
            return $product->price * $i['quantity'];
        });

        $snapToken = \Midtrans\Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
        ]);
    }

    public function finalize(string $orderId)
    {
        $sale = Sale::where('order_id', $orderId)
            ->where('transaction_status', 'pending')
            ->firstOrFail();

        return DB::transaction(function () use ($sale) {

            $items = json_decode($sale->items_snapshot, true);

            $totalAmount = 0;
            $totalCost   = 0;

            foreach ($items as $item) {

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
                'total_amount'       => $totalAmount,
                'total_cost'         => $totalCost,
                'profit'             => $totalAmount - $totalCost,
                'transaction_status' => 'paid',
            ]);

            return response()->json(['message' => 'Transaction finalized']);
        });
    }



    // STATUS CHECK 
    public function checkStatus(string $orderId)
    {
        $status = (object) Transaction::status($orderId);

        $sale = Sale::where('order_id', $orderId)->firstOrFail();

        if (in_array($status->transaction_status, ['settlement', 'capture'])) {
            $this->midtransService->processPaidTransaction($sale);
        }

        return response()->json($status);
    }
}
