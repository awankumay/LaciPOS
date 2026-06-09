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
}
