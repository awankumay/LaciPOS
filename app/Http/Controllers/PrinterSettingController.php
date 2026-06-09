<?php

namespace App\Http\Controllers;

use App\Models\StoreProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PrinterSettingController extends Controller
{
    public function index()
    {
        $profile = StoreProfile::getProfile();
        
        $printers = [];
        if (class_exists(\Native\Laravel\Facades\System::class)) {
            try {
                $nativePrinters = \Native\Laravel\Facades\System::printers();
                foreach ($nativePrinters as $printer) {
                    $printers[] = [
                        'name' => $printer->name,
                        'displayName' => $printer->displayName ?? $printer->name,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Could not fetch printers from NativePHP: ' . $e->getMessage());
            }
        }

        return Inertia::render('Settings/Printer', [
            'profile' => $profile,
            'printers' => $printers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'printer_name' => 'nullable|string|max:255',
            'paper_size' => 'required|in:58mm,80mm',
            'auto_print' => 'required|boolean',
        ]);

        $profile = StoreProfile::getProfile();
        if (!$profile) {
            $profile = StoreProfile::create($validated);
        } else {
            $profile->update($validated);
        }

        return redirect()->back()->with('success', 'Pengaturan printer berhasil disimpan.');
    }

    public function testPrint(\App\Services\PrintService $printService)
    {
        $dummyOrder = new \App\Models\Order([
            'order_number' => 'TEST-' . date('Ymd-His'),
            'payment_method' => 'cash',
            'payment_provider' => null,
            'total_amount' => 150000,
            'cash_received' => 150000,
            'change_amount' => 0,
        ]);
        $dummyOrder->created_at = now();

        $dummyUser = new \App\Models\User(['name' => 'Test Cashier']);
        $dummyOrder->setRelation('user', $dummyUser);

        $dummyItem = new \App\Models\OrderItem([
            'product_name_snapshot' => 'Produk Test Printer',
            'variant_label' => 'Variant 1',
            'quantity' => 1,
            'snapshot_price' => 150000,
            'subtotal' => 150000,
        ]);
        $dummyOrder->setRelation('items', collect([$dummyItem]));

        $result = $printService->printReceipt($dummyOrder);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Test print berhasil dikirim ke printer.');
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
}
