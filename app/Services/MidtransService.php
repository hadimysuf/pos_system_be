<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class MidtransService
{
    public function processPaidTransaction(Sale $sale)
    {
        if ($sale->transaction_status === 'paid') {
            return;
        }

        DB::transaction(function () use ($sale) {

            $items = json_decode($sale->items_snapshot, true);

            if (!$items || count($items) === 0) {
                throw new \Exception('Items snapshot empty');
            }

            $totalAmount = 0;
            $totalCost   = 0;

            foreach ($items as $item) {

                if (!isset($item['product_id'], $item['quantity'])) {
                    throw new \Exception('Invalid item format');
                }

                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw new \Exception('Product not found');
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception('Stock not enough');
                }

                $subtotal = $product->price * $item['quantity'];
                $cost     = $product->cost * $item['quantity'];

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                    'cost'       => $product->cost,
                    'subtotal'   => $subtotal,
                    'profit'     => $subtotal - $cost,
                ]);

                $product->decrement('stock', $item['quantity']);

                $totalAmount += $subtotal;
                $totalCost   += $cost;
            }

            $sale->update([
                'transaction_status' => 'paid',
                'total_amount'       => $totalAmount,
                'total_cost'         => $totalCost,
                'profit'             => $totalAmount - $totalCost,
               
            ]);
        });
    }
}
