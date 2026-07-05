<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba/Rugi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .store-name { font-size: 24px; font-weight: bold; margin: 0; }
        .store-address { font-size: 14px; margin: 5px 0 10px 0; color: #666; }
        .report-title { font-size: 18px; font-weight: bold; margin: 0; }
        .meta-table { width: 100%; margin-bottom: 30px; }
        .meta-table td { padding: 2px 0; }
        
        .summary-box { width: 100%; border: 1px solid #ddd; margin-bottom: 20px; border-radius: 5px; }
        .summary-box th { background-color: #f4f4f4; padding: 10px; text-align: left; font-size: 14px; width: 40%; }
        .summary-box td { padding: 10px; font-size: 14px; font-weight: bold; text-align: right; width: 60%; }
        
        .profit-box th, .profit-box td { background-color: #e8f5e9; font-size: 16px; border-top: 2px solid #4caf50; }
        .loss-box th, .loss-box td { background-color: #ffebee; font-size: 16px; border-top: 2px solid #f44336; }
        
        .disclaimer { margin-top: 40px; padding: 15px; background-color: #e3f2fd; border: 1px solid #90caf9; border-radius: 5px; font-size: 11px; color: #0d47a1; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="store-name">{{ $store_name }}</h1>
        <p class="store-address">{{ $store_address }}</p>
        <h2 class="report-title">Laporan Laba/Rugi</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Periode:</strong> {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</td>
            <td style="text-align: right;"><strong>Tanggal Cetak:</strong> {{ $print_date }}</td>
        </tr>
    </table>

    <table class="summary-box" cellspacing="0">
        <tr>
            <th>Total Pendapatan (Penjualan)</th>
            <td>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Harga Pokok Penjualan (HPP / Modal)</th>
            <td style="color: #d32f2f;">- Rp {{ number_format($totalCogs, 0, ',', '.') }}</td>
        </tr>
        <tr class="{{ $grossProfit >= 0 ? 'profit-box' : 'loss-box' }}">
            <th>Laba Kotor (Gross Profit)</th>
            <td style="{{ $grossProfit >= 0 ? 'color: #2e7d32;' : 'color: #c62828;' }}">
                Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <th>Total Biaya Admin (MDR)</th>
            <td style="color: #d32f2f;">- Rp {{ number_format($totalMdr, 0, ',', '.') }}</td>
        </tr>
        <tr class="{{ $netProfit >= 0 ? 'profit-box' : 'loss-box' }}">
            <th>Laba Bersih (Net Profit)</th>
            <td style="{{ $netProfit >= 0 ? 'color: #2e7d32;' : 'color: #c62828;' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
                <span style="font-size: 12px; margin-left: 10px;">(Margin: {{ $marginPercentage }}%)</span>
            </td>
        </tr>
    </table>

    <div class="disclaimer">
        <strong>* Catatan Penting:</strong><br>
        Laporan ini menampilkan <strong>Laba Bersih</strong> setelah dikurangi Biaya Admin (MDR) dari pembayaran non-tunai. Angka di atas belum dikurangi dengan biaya-biaya operasional toko (seperti listrik, sewa, gaji karyawan, dan lain-lain).
    </div>
</body>
</html>
