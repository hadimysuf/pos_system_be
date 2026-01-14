<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Sale;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set Midtrans config
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    public function createTransaction(Request $request)
    {
        // Ambil user yang login via Sanctum
        $user = Auth::user(); // <--- aman karena route pakai auth:sanctum

        if (!$user) {
            return response()->json(['error' => 'User tidak terautentikasi'], 401);
        }

        $orderId = 'INV-' . time();

        // Simpan data ke database
        $sale = Sale::create([
            'invoice_number'     => $orderId,
            'order_id'           => $orderId,
            'user_id'            => $user->id,   // <--- otomatis id user kasir
            'sale_date'          => now(),
            'total_amount'       => $request->amount,
            'total_cost'         => 0,
            'profit'             => 0,
            'payment_method'     => 'midtrans',
            'transaction_status' => 'pending',
        ]);

        // Parameter Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
        ];

        // Generate SNAP TOKEN
        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $orderId,
            'amount' => $request->amount
        ]);
    }

    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');

        // Verify signature
        $signature = hash(
            'sha512',
            $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
        );

        if ($signature != $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Cari sale berdasarkan order_id
        $sale = Sale::where('order_id', $request->order_id)->first();

        if (!$sale) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Update status transaksi berdasarkan response Midtrans
        $sale->transaction_status = $request->transaction_status;
        $sale->payment_type       = $request->payment_type;
        $sale->fraud_status       = $request->fraud_status;
        $sale->transaction_id     = $request->transaction_id;
        $sale->transaction_time   = $request->transaction_time;
        $sale->save();

        return response()->json([
            'message' => 'Callback processed',
            'status'  => $request->transaction_status
        ]);
    }
}
