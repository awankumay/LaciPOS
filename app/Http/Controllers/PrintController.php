<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreProfile;
use App\Services\PrintService;
use App\Services\ReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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

        // Konversi logo ke base64 agar bisa dirender dompdf
        if (!empty($data['store']['logo_url']) && $store?->logo_path) {
            $fullPath = Storage::disk('public')->path($store->logo_path);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath);
                $base64 = base64_encode(file_get_contents($fullPath));
                $data['store']['logo_url'] = "data:{$mime};base64,{$base64}";
            }
        }

        $widthMm  = $paperSize === '58mm' ? 58 : 80;
        $widthPt  = $widthMm * 2.835;

        $itemCount = count($data['items']);
        $heightPt = $paperSize === '58mm'
            ? max(350, 280 + ($itemCount * 22))
            : max(400, 320 + ($itemCount * 26));

        $pdf = Pdf::loadView('receipts.thermal', $data)
            ->setPaper([0, 0, $widthPt, $heightPt], 'portrait');

        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', false);
        $pdf->getDomPDF()->set_option('dpi', 203);

        $filename = 'struk-' . $order->order_number . '.pdf';

        return $pdf->download($filename);
    }
}
