<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;
use App\Models\Sale;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Support\Str;

class MidtransController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = false; // ubah true saat production
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE SNAP TOKEN
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        $orderId = 'INV-' . Str::uuid();
        $user    = $request->user();

        // Hitung total
        $grossAmount = collect($request->items)->sum(function ($i) {
            $product = Product::findOrFail($i['product_id']);
            return $product->price * $i['quantity'];
        });

        // Simpan transaksi pending
        $sale = Sale::create([
            'invoice_number'     => $orderId,
            'order_id'           => $orderId,
            'user_id'            => $user->id,
            'sale_date'          => now(),
            'payment_method'     => 'midtrans',
            'transaction_status' => 'pending',
            'items_snapshot'     => json_encode($request->items),
            'total_amount'       => $grossAmount
        ]);

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
        ]);
    }

    public function callback(Request $request)
    {
        try {

            Log::info('Midtrans RAW Callback', $request->all());

            $orderId           = $request->input('order_id');
            $statusCode        = $request->input('status_code');
            $grossAmount       = $request->input('gross_amount');
            $transactionStatus = $request->input('transaction_status');
            $fraudStatus       = $request->input('fraud_status');
            $signatureKey      = $request->input('signature_key');

            // 🔐 Verify Signature
            $serverKey = config('midtrans.server_key');
            $expectedSignature = hash(
                'sha512',
                $orderId . $statusCode . $grossAmount . $serverKey
            );

            if ($expectedSignature !== $signatureKey) {
                Log::error('Invalid signature');
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $sale = Sale::where('order_id', $orderId)->first();

            if (!$sale) {
                return response()->json(['message' => 'Sale not found'], 404);
            }

            /*
        |--------------------------------------------
        | SUCCESS
        |--------------------------------------------
        */
            if (
                $transactionStatus === 'settlement' ||
                ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            ) {

                $this->midtransService->processPaidTransaction($sale);

                $sale->update([
                    'payment_type'      => $request->input('payment_type'),
                    'transaction_id'    => $request->input('transaction_id'),
                    'transaction_time'  => $request->input('transaction_time'),
                    'fraud_status'      => $fraudStatus,
                ]);

                Log::info('Transaction marked as PAID');
            }

            /*
        |--------------------------------------------
        | FAILED
        |--------------------------------------------
        */
            if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $sale->update([
                    'transaction_status' => $transactionStatus,
                ]);
            }

            Log::info('=== MIDTRANS CALLBACK END ===');

            return response()->json(['message' => 'Callback handled']);
        } catch (\Exception $e) {

            Log::error('Midtrans Callback Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MANUAL STATUS CHECK (OPTIONAL BACKUP)
    |--------------------------------------------------------------------------
    */
    public function checkStatus(string $orderId)
    {
        $status = (object) Transaction::status($orderId);

        $sale = Sale::where('order_id', $orderId)->firstOrFail();

        if (in_array($status->transaction_status, ['settlement', 'capture'])) {
            $this->midtransService->processPaidTransaction($sale);

            $sale->update([
                'transaction_status' => 'paid'
            ]);
        }

        return response()->json($status);
    }
}
