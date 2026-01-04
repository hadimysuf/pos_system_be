<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * =========================
     * 📦 LIST STOK GUDANG
     * =========================
     */
    public function index()
    {
        return Product::select(
            'id',
            'sku',
            'name',
            'stock',
            'low_stock_threshold',
            'max_stock'
        )->orderBy('name')->get();
    }

    /**
     * =========================
     * 📥 PENGADAAN STOK (IN)
     * =========================
     */
    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'note'       => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {

            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            $qty = $request->quantity;

            // 🚫 Cek kapasitas gudang
            if ($product->stock + $qty > $product->max_stock) {
                return response()->json([
                    'message' => 'Stok melebihi kapasitas gudang',
                    'current_stock' => $product->stock,
                    'max_stock' => $product->max_stock
                ], 422);
            }

            // Update stok
            $product->increment('stock', $qty);

            // Log stok
            StockLog::create([
                'product_id' => $product->id,
                'user_id'    => $request->user()->id,
                'type'       => 'IN',
                'change'     => $qty,
                'note'       => $request->note ?? 'Pengadaan barang'
            ]);

            return response()->json([
                'message' => 'Stok berhasil ditambahkan',
                'stock'   => $product->stock
            ]);
        });
    }

    /**
     * =========================
     * 📤 STOK KELUAR (RUSAK / RETUR)
     * =========================
     */
    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'note'       => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {

            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            $qty = $request->quantity;

            if ($product->stock < $qty) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi'
                ], 422);
            }

            $product->decrement('stock', $qty);

            StockLog::create([
                'product_id' => $product->id,
                'user_id'    => $request->user()->id,
                'type'       => 'OUT',
                'change'     => -$qty,
                'note'       => $request->note ?? 'Stok keluar gudang'
            ]);

            return response()->json([
                'message' => 'Stok berhasil dikurangi',
                'stock'   => $product->stock
            ]);
        });
    }

    /**
     * =========================
     * ⚠️ STOK MENIPIS
     * =========================
     */
    public function lowStock()
    {
        return Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock')
            ->get();
    }

    /**
     * =========================
     * 🧾 RIWAYAT STOK (AUDIT)
     * =========================
     */
    public function logs(Request $request)
    {
        $query = StockLog::with(['product', 'user']);

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        return $query->latest()->paginate(15);
    }
}
