<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
        }

        .section {
            margin-bottom: 15px;
        }

        .summary-table, .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px;
        }

        .transaction-table th,
        .transaction-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        .transaction-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>LAPORAN TRANSAKSI PENJUALAN</h2>
        <p><strong>Nama Toko</strong></p>
        <p>Alamat Toko | Telp: 08xxxxxxxx</p>
    </div>

    {{-- PERIODE --}}
    <div class="section">
        <strong>Periode:</strong>
        {{ $from }} s/d {{ $to }}
    </div>

    {{-- SUMMARY --}}
    <div class="section">
        <table class="summary-table">
            <tr>
                <td>Total Transaksi</td>
                <td>: {{ $summary['total_transactions'] }}</td>
            </tr>
            <tr>
                <td>Total Penjualan</td>
                <td>: Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Modal</td>
                <td>: Rp {{ number_format($summary['total_cost'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Profit</td>
                <td>: Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- TABEL TRANSAKSI --}}
    <div class="section">
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $index => $sale)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $sale['invoice'] }}</td>
                        <td class="text-center">{{ $sale['date'] }}</td>
                        <td>{{ $sale['cashier'] }}</td>
                        <td class="text-right">
                            Rp {{ number_format($sale['total'], 0, ',', '.') }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($sale['profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
    </div>

</body>
</html>
