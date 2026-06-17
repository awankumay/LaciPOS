<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreProfile;
use App\Services\PrintService;
use App\Services\ReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function print(Order $order, PrintService $printService)
    {
        $printService->printReceipt($order);

        return redirect()->back()->with('print_success', 'Perintah cetak struk telah dikirim ke printer.');
    }

    public function download(Order $order, ReceiptService $receiptService)
    {
        $store = StoreProfile::getProfile();
        $paperSize = $store?->paper_size ?? '58mm';

        $data = $receiptService->generateReceiptData($order);
        $data['paperSize'] = $paperSize;

        $widthMm  = $paperSize === '58mm' ? 58 : 80;
        // dompdf setPaper array menggunakan satuan point (1mm = 2.835pt)
        $widthPt  = $widthMm * 2.835;
        $heightPt = 1000; // ~353mm — cukup untuk struk panjang apapun

        $pdf = Pdf::loadView('receipts.thermal', $data)
            ->setPaper([0, 0, $widthPt, $heightPt], 'portrait');

        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', false);
        $pdf->getDomPDF()->set_option('dpi', 203); // standard thermal DPI

        $filename = 'struk-' . $order->order_number . '.pdf';

        return $pdf->download($filename);
    }
}
