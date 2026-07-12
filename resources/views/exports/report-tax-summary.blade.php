<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pajak & Service Charge</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .store-name { font-size: 24px; font-weight: bold; margin: 0; }
        .store-address { font-size: 14px; margin: 5px 0 10px 0; color: #666; }
        .report-title { font-size: 18px; font-weight: bold; margin: 0; }
        .report-meta { text-align: right; font-size: 10px; color: #777; margin-bottom: 10px; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 2px 0; }
        .summary-box { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-box td { padding: 8px 12px; border: 1px solid #ddd; font-size: 13px; }
        .summary-box td.label { background-color: #f4f4f4; font-weight: bold; width: 40%; }
        .summary-box td.value { text-align: right; font-weight: bold; width: 60%; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f4f4f4; font-weight: bold; text-align: center; font-size: 11px; }
        table.data-table td.text-right { text-align: right; }
        table.data-table td.text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="store-name">{{ $store_name }}</h1>
        <p class="store-address">{{ $store_address }}</p>
        <h2 class="report-title">Laporan Pajak & Service Charge</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Periode:</strong> {{ \Carbon\Carbon::parse($start_date)->locale('id')->translatedFormat('l, d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->locale('id')->translatedFormat('l, d M Y') }}</td>
            <td style="text-align: right;"><strong>Tanggal Cetak:</strong> {{ $print_date }}</td>
        </tr>
    </table>

    <table class="summary-box" cellspacing="0">
        <tr>
            <td class="label">Total Pajak Dipungut</td>
            <td class="value" style="color: #1565c0;">Rp {{ number_format($totalTax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Service Charge Terkumpul</td>
            <td class="value" style="color: #7b1fa2;">Rp {{ number_format($totalSc, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Subtotal</th>
                <th>Tax Rate</th>
                <th>Tax Amount</th>
                <th>Service Charge</th>
                <th>Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row['date'])->locale('id')->translatedFormat('l, d M Y') }}</td>
                <td>{{ $row['order_number'] }}</td>
                <td class="text-right">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $row['tax_rate'] }}</td>
                <td class="text-right">Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($row['service_charge_amount'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($row['grand_total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>