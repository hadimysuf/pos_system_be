<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi #{{ $transaction->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 24px;
        }

        /* ===== HEADER ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company h1 {
            margin: 0;
            font-size: 18px;
        }

        .company p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h2 {
            margin: 0;
            font-size: 16px;
        }

        .doc-title p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #666;
        }

        /* ===== INFO ===== */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }

        .info-table td.label {
            width: 35%;
            color: #666;
        }

        .info-table td.value {
            font-weight: bold;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            font-size: 11px;
            text-transform: uppercase;
        }

        td {
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        /* ===== SUMMARY ===== */
        .summary {
            margin-top: 15px;
            width: 100%;
        }

        .summary td {
            padding: 6px;
        }

        .summary .total-label {
            text-align: right;
            font-weight: bold;
        }

        .summary .total-value {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            font-size: 10px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <div class="company">
            <h1>POS SYSTEM</h1>
            <p>Jl. Sari Asih no.54</p>
            <p>Telp: 0812-3456-7890</p>
        </div>

        <div class="doc-title">
            <h2>DETAIL TRANSAKSI</h2>
            <p>#{{ $transaction->id }}</p>
        </div>
    </div>

    {{-- ================= INFO TRANSAKSI ================= --}}
    <table class="info-table">
        <tr>
            <td class="label">Tanggal</td>
            <td class="value">
                {{ $transaction->created_at->format('d M Y H:i') }}
            </td>
        </tr>
        <tr>
            <td class="label">Kasir</td>
            <td class="value">
                {{ $transaction->cashier->name ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td class="value">
                {{ strtoupper($transaction->payment_method) }}
            </td>
        </tr>
        <tr>
            <td class="label">Status Transaksi</td>
            <td class="value">
                {{ strtoupper($transaction->status) }}
            </td>
        </tr>
    </table>

    {{-- ================= ITEM TABLE ================= --}}
    <table>
        <thead>
        <tr>
            <th>Produk</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($transaction->items as $item)
            <tr>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="text-right">
                    Rp {{ number_format($item->price, 0, ',', '.') }}
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">
                    Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- ================= SUMMARY ================= --}}
    <table class="summary">
        <tr>
            <td class="total-label">TOTAL TRANSAKSI</td>
            <td class="total-value">
                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        Dicetak pada {{ now()->format('d M Y H:i') }} • Sistem POS
    </div>

</div>

</body>
</html>
