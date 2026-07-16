<?php
namespace App\Services;

use App\Models\Order;
use App\Models\StoreProfile;
use Illuminate\Support\Facades\Log;

class PrintService
{
    private ReceiptService $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    public function printReceipt(Order $order): array
    {
        $store = StoreProfile::getProfile();
        $paperSize = $store?->paper_size ?? '58mm';

        try {
            $html = $this->receiptService->renderReceiptHtml($order, $paperSize);
            
            // Encode only specific characters that break data: URIs natively
            // '#' breaks the URL (acts as fragment) and '%' causes invalid encoding exception
            // Also STRIP ALL NEWLINES because unescaped newlines in a data URI will cause
            // Chromium's URL parser to silently abort or truncate the document!
            $html = str_replace(["\r", "\n"], '', $html);
            $html = str_replace(['%', '#'], ['%25', '%23'], $html);

            if (class_exists(\Native\Laravel\Facades\System::class)) {
                $targetPrinter = null;
                if (!empty($store?->printer_name)) {
                    $printers = \Native\Laravel\Facades\System::printers();
                    $targetPrinter = collect($printers)->firstWhere('name', $store->printer_name);
                    
                    if (!$targetPrinter && class_exists(\Native\Laravel\DataObjects\Printer::class)) {
                        $targetPrinter = new \Native\Laravel\DataObjects\Printer($store->printer_name, $store->printer_name, '', 0, false, []);
                    }
                }
                
                $widthMicrons = $paperSize === '58mm' ? 58000 : 80000;
                
                $client = app(\Native\Laravel\Client\Client::class);
                $response = $client->post('system/print', [
                    'html' => $html,
                    'printer' => $targetPrinter->name ?? '',
                    'settings' => [
                        'silent' => true,
                        'printBackground' => false,
                        'margins' => [
                            'marginType' => 'none',
                        ],
                        'pageSize' => [
                            'width' => $widthMicrons,
                            'height' => 3276000
                        ]
                    ]
                ]);
            } else {
                Log::info("Print function is bypassed because NativePHP is not available in current environment. HTML length: " . strlen($html));
            }

            return ['success' => true, 'message' => 'Struk berhasil dicetak.'];
        } catch (\Exception $e) {
            Log::error('Print error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Printer tidak terhubung. Struk tidak dicetak, tetapi transaksi tetap tersimpan.',
            ];
        }
    }
}
 