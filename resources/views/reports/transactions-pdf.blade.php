<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 24px;
        }

        /* ===== HEADER ===== */
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* ===== META ===== */
        .meta {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .meta span {
            display: inline-block;
            margin-right: 20px;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 7px;
        }

        th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        /* ===== SUMMARY ===== */
        .summary {
            margin-top: 20px;
            width: 100%;
        }

        .summary td {
            padding: 6px;
            border: none;
        }

        .summary .label {
            text-align: right;
            font-weight: bold;
        }

        .summary .value {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 40px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
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
                <p>Laporan Transaksi Penjualan</p>
                <p>Jl. Sari Asih no.54 • Telp: 0812-3456-7890</p>
            </div>

            <div class="doc-title">
                <h2>LAPORAN TRANSAKSI</h2>
                <p>Dicetak {{ now()->format('d M Y H:i') }}</p>
            </div>
        </div>

        {{-- ================= META ================= --}}
        <div class="meta">
            <span>
                <strong>Periode:</strong>
                @if (!empty($filters['start_date']) && !empty($filters['end_date']))
                    {{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}
                @else
                    Semua tanggal
                @endif
            </span>
        </div>

        {{-- ================= TABLE ================= --}}
        <table>
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="25%">Tanggal</th>
                    <th width="35%">Kasir</th>
                    <th width="30%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $sale->cashier->name ?? '-' }}</td>
                        <td class="text-right">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center">
                            Tidak ada data transaksi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ================= SUMMARY ================= --}}
        <table class="summary">
            <tr>
                <td class="label">TOTAL TRANSAKSI</td>
                <td class="value">{{ count($sales) }}</td>
            </tr>
            <tr>
                <td class="label">TOTAL REVENUE</td>
                <td class="value">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        {{-- ================= FOOTER ================= --}}
        <div class="footer">
            Laporan ini dihasilkan secara otomatis oleh sistem POS • Bersifat rahasia
        </div>

    </div>
</body>

</html>
