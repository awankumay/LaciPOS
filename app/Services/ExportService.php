<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    public function exportRevenuePdf(array $data)
    {
        $pdf = Pdf::loadView('exports.report-revenue', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('laporan-pendapatan.pdf');
    }

    public function exportProfitLossPdf(array $data)
    {
        $pdf = Pdf::loadView('exports.report-profit', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('laporan-laba-rugi.pdf');
    }

    public function exportRevenueCsv($dailyRevenue, string $startDate, string $endDate): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "laporan-pendapatan-{$startDate}-{$endDate}.csv";

        return response()->streamDownload(function () use ($dailyRevenue) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($handle, ['Tanggal', 'Jumlah Transaksi', 'Total Pendapatan']);

            foreach ($dailyRevenue as $row) {
                fputcsv($handle, [
                    $row->date,
                    $row->total_transactions,
                    $row->total_revenue,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportProfitLossCsv(array $data, string $startDate, string $endDate): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "laporan-laba-rugi-{$startDate}-{$endDate}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($handle, ['Metrik', 'Nilai']);
            fputcsv($handle, ['Total Pendapatan (Penjualan)', $data['totalRevenue']]);
            fputcsv($handle, ['Total Harga Pokok Penjualan (HPP)', $data['totalCogs']]);
            fputcsv($handle, ['Laba Kotor (Gross Profit)', $data['grossProfit']]);
            fputcsv($handle, ['Margin (%)', $data['marginPercentage']]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportBestSellersCsv($products, string $startDate, string $endDate): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "laporan-produk-terlaris-{$startDate}-{$endDate}.csv";

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($handle, ['No', 'Nama Produk', 'Qty Terjual', 'Total Pendapatan']);

            foreach ($products as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row->product_name,
                    $row->total_qty,
                    $row->total_revenue,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
