<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .store-name { font-size: 22px; font-weight: bold; margin: 0; }
        .store-address { font-size: 12px; margin: 5px 0 10px 0; color: #666; }
        .report-title { font-size: 16px; font-weight: bold; margin: 0; }
        .report-meta { text-align: right; font-size: 10px; color: #777; margin-bottom: 10px; }
        .meta-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .meta-table td { padding: 2px 0; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 5px 4px; text-align: left; }
        table.data-table th { background-color: #f4f4f4; font-weight: bold; text-align: center; }
        table.data-table td.text-right { text-align: right; }
        table.data-table td.text-center { text-align: center; }
        .grand-total-row th { background-color: #eaeaea; font-size: 11px; font-weight: bold; text-align: right; padding-right: 10px; }
        .grand-total-row td { background-color: #eaeaea; font-size: 11px; font-weight: bold; text-align: right; }
        .note { font-size: 8px; color: #888; margin-top: 10px; font-style: italic; }
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
                <th rowspan="2">No.</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Transaksi</th>
                <th colspan="6">Rincian Pendapatan (Rp)</th>
            </tr>
            <tr>
                <th>Omzet Kotor</th>
                <th>Pajak Dipungut</th>
                <th>Service Charge</th>
                <th>Pendapatan Bersih</th>
                <th>Pot. MDR</th>
                <th>Total Dana Cair</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyRevenue as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row['date'])->locale('id')->translatedFormat('l, d M Y') }}</td>
                <td class="text-right">{{ $row['total_transactions'] }}</td>
                <td class="text-right">{{ number_format($row['gross_revenue'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row['total_tax'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row['total_sc'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row['net_revenue'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row['total_mdr'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row['total_settlement'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
            <tr class="grand-total-row">
                <th colspan="3">Grand Total</th>
                <td>{{ number_format($totals['gross_revenue'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['total_tax'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['total_sc'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['net_revenue'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['total_mdr'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['total_settlement'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="note">
        Pajak Dipungut = uang titipan negara (PPN) yang dikumpulkan dari pelanggan.<br>
        Service Charge = biaya jasa pelayanan untuk karyawan.<br>
        Pendapatan Bersih = Omzet Kotor - Pajak - Service Charge (murni hak milik toko).<br>
        Potongan MDR = biaya admin payment gateway (QRIS/transfer).<br>
        Total Dana Cair = Omzet Kotor - Potongan MDR (untuk rekonsiliasi bank).
    </p>
</body>
</html>
