<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    /**
     * 📋 List all purchase orders
     * Optional filter: status, supplier_id, date range
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('order_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        return $query->latest()->paginate(10);
    }

    /**
     * 📝 Create new Purchase Order
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'expected_arrival' => 'nullable|date',
            'note'             => 'nullable|string',
        ]);

        $totalQty = collect($request->items)->sum('quantity');

        $po = PurchaseOrder::create([
            'supplier_id'     => $request->supplier_id,
            'user_id'         => $request->user()->id,
            'order_date'      => now(),
            'expected_arrival' => $request->expected_arrival ?? now()->addDays(7),
            'status'          => 'PENDING',
            'items'           => $request->items,
            'total_quantity'  => $totalQty,
            'note'            => $request->note
        ]);

        return response()->json([
            'message' => 'Purchase order created',
            'data'    => $po
        ]);
    }

    /**
     * ✏️ Edit Purchase Order (allowed only if still PENDING)
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'PENDING') {
            return response()->json([
                'message' => 'Only PENDING orders can be updated'
            ], 422);
        }

        $request->validate([
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'expected_arrival' => 'date|nullable',
            'note' => 'string|nullable'
        ]);

        if ($request->items) {
            $purchaseOrder->total_quantity = collect($request->items)->sum('quantity');
            $purchaseOrder->items = $request->items;
        }

        if ($request->expected_arrival) {
            $purchaseOrder->expected_arrival = $request->expected_arrival;
        }

        if ($request->note !== null) {
            $purchaseOrder->note = $request->note;
        }

        $purchaseOrder->save();

        return response()->json([
            'message' => 'Purchase order updated',
            'data'    => $purchaseOrder
        ]);
    }

    /**
     * ✔️ Approve Purchase Order
     * Update stock + create StockLog
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'PENDING') {
            return response()->json([
                'message' => 'This PO cannot be approved'
            ], 422);
        }

        return DB::transaction(function () use ($purchaseOrder) {

            foreach ($purchaseOrder->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = $item['quantity'];

                $product->increment('stock', $qty);

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => request()->user()->id,
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'type'       => 'IN',
                    'change'     => $qty,
                    'note'       => 'Approved Purchase Order #' . $purchaseOrder->id
                ]);
            }

            $purchaseOrder->update([
                'status' => 'APPROVED',
                'approved_at' => now()
            ]);

            return response()->json([
                'message' => 'PO Approved & stock updated'
            ]);
        });
    }

    /**
     * ❌ Cancel Purchase Order
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'PENDING') {
            return response()->json([
                'message' => 'Only PENDING orders can be cancelled'
            ], 422);
        }

        $purchaseOrder->update(['status' => 'CANCELED']);

        return response()->json([
            'message' => 'Purchase order canceled'
        ]);
    }

    /**
     * 🔍 View PO detail
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->load(['supplier']);
    }
}
