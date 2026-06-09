<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .store-name { font-size: 24px; font-weight: bold; margin: 0; }
        .store-address { font-size: 14px; margin: 5px 0 10px 0; color: #666; }
        .report-title { font-size: 18px; font-weight: bold; margin: 0; }
        .report-meta { text-align: right; font-size: 10px; color: #777; margin-bottom: 10px; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 2px 0; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f4f4f4; font-weight: bold; text-align: center; }
        table.data-table td.text-right { text-align: right; }
        table.data-table td.text-center { text-align: center; }
        .grand-total-row th { background-color: #eaeaea; font-size: 14px; text-align: right; padding-right: 15px; }
        .grand-total-row td { background-color: #eaeaea; font-size: 14px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="store-name">{{ $store_name }}</h1>
        <p class="store-address">{{ $store_address }}</p>
        <h2 class="report-title">Laporan Pendapatan</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Periode:</strong> {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</td>
            <td style="text-align: right;"><strong>Tanggal Cetak:</strong> {{ $print_date }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jumlah Transaksi</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyRevenue as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('l, d M Y') }}</td>
                <td class="text-right">{{ $row->total_transactions }}</td>
                <td class="text-right">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
            <tr class="grand-total-row">
                <th colspan="3">Grand Total Pendapatan</th>
                <td>Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
