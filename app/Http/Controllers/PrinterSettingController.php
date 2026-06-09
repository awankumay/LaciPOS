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
}
