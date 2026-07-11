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

    public function exportRevenueCsv($dailyRevenue, array $totals, string $startDate, string $endDate): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "laporan-pendapatan-{$startDate}-{$endDate}.csv";

        return response()->streamDownload(function () use ($dailyRevenue, $totals) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($handle, ['Tanggal', 'Jumlah Transaksi', 'Omzet Kotor', 'Pajak Dipungut', 'Service Charge', 'Pendapatan Bersih', 'Potongan MDR', 'Total Dana Cair']);

            foreach ($dailyRevenue as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['total_transactions'],
                    $row['gross_revenue'],
                    $row['total_tax'],
                    $row['total_sc'],
                    $row['net_revenue'],
                    $row['total_mdr'],
                    $row['total_settlement'],
                ]);
            }

            fputcsv($handle, ['GRAND TOTAL', $totals['total_transactions'], $totals['gross_revenue'], $totals['total_tax'], $totals['total_sc'], $totals['net_revenue'], $totals['total_mdr'], $totals['total_settlement']]);

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
            fputcsv($handle, ['Total Biaya Admin (MDR)', $data['totalMdr']]);
            fputcsv($handle, ['Laba Bersih (Net Profit)', $data['netProfit']]);
            fputcsv($handle, ['Margin (%)', $data['marginPercentage']]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportTaxSummaryPdf(array $data)
    {
        $pdf = Pdf::loadView('exports.report-tax-summary', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('laporan-pajak-service-charge.pdf');
    }

    public function exportTaxSummaryCsv($details, string $startDate, string $endDate, float $totalTax, float $totalSc): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "laporan-pajak-service-charge-{$startDate}-{$endDate}.csv";

        return response()->streamDownload(function () use ($details, $totalTax, $totalSc) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Ringkasan']);
            fputcsv($handle, ['Total Pajak Dipungut', $totalTax]);
            fputcsv($handle, ['Total Service Charge Terkumpul', $totalSc]);
            fputcsv($handle, []);

            fputcsv($handle, ['Tanggal', 'No. Invoice', 'Subtotal', 'Tax Rate', 'Tax Amount', 'Service Charge', 'Grand Total']);

            foreach ($details as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['order_number'],
                    $row['subtotal'],
                    $row['tax_rate'],
                    $row['tax_amount'],
                    $row['service_charge_amount'],
                    $row['grand_total'],
                ]);
            }

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
