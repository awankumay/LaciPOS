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
        $dummyUser = new \App\Models\User(['name' => 'Kasir Test']);
        $dummyUser->id = 'test-user-id';

        $dummyItem1 = new \App\Models\OrderItem([
            'product_name_snapshot' => 'Kopi Susu',
            'variant_label' => null,
            'quantity' => 2,
            'snapshot_price' => 12000,
            'snapshot_discount_amount' => 2000,
            'subtotal' => 20000,
        ]);

        $dummyItem2 = new \App\Models\OrderItem([
            'product_name_snapshot' => 'Es Teh Manis',
            'variant_label' => null,
            'quantity' => 1,
            'snapshot_price' => 7000,
            'snapshot_discount_amount' => 0,
            'subtotal' => 7000,
        ]);

        $dummyItem3 = new \App\Models\OrderItem([
            'product_name_snapshot' => 'Nasi Goreng',
            'variant_label' => 'Spesial',
            'quantity' => 1,
            'snapshot_price' => 25000,
            'snapshot_discount_amount' => 0,
            'subtotal' => 25000,
        ]);

        $items = collect([$dummyItem1, $dummyItem2, $dummyItem3]);

        $dummyOrder = new \App\Models\Order();
        $dummyOrder->order_number = 'TEST-' . date('Ymd-His');
        $dummyOrder->subtotal = 52000;
        $dummyOrder->payment_method = 'cash';
        $dummyOrder->payment_provider = null;
        $dummyOrder->total_amount = 55000;
        $dummyOrder->cash_received = 100000;
        $dummyOrder->change_amount = 45000;
        $dummyOrder->created_at = now();
        $dummyOrder->setRelation('user', $dummyUser);
        $dummyOrder->setRelation('items', $items);

        $result = $printService->printReceipt($dummyOrder);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Test print berhasil dikirim ke printer.');
        } else {
            return redirect()->back()->with('warning', $result['message']);
        }
    }
}
