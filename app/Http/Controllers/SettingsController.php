<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingRequest;
use App\Models\StoreProfile;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function store()
    {
        $profile = StoreProfile::getProfile();

        return Inertia::render('Settings/Store', [
            'profile' => $profile ? [
                'store_name' => $profile->store_name,
                'address' => $profile->address,
                'phone' => $profile->phone,
                'logo_url' => $profile->logo_url,
                'receipt_footer' => $profile->receipt_footer,
            ] : null,
        ]);
    }

    public function updateStore(OnboardingRequest $request)
    {
        $data = [
            'store_name' => $request->validated('store_name'),
            'address' => $request->validated('address'),
            'phone' => $request->validated('phone'),
            'receipt_footer' => $request->validated('receipt_footer'),
        ];

        if ($request->hasFile('logo')) {
            $profile = StoreProfile::getProfile();
            if ($profile?->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $profile = StoreProfile::getProfile();
            if ($profile?->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }
            $data['logo_path'] = null;
        }

        StoreProfile::updateOrCreate([], $data);

        return back()->with('success', 'Informasi toko berhasil diperbarui.');
    }
    public function inventory()
    {
        $profile = StoreProfile::getProfile();
        return Inertia::render('Settings/Inventory', [
            'defaultMinStock' => $profile?->default_min_stock ?? 5,
        ]);
    }

    public function updateInventory(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'default_min_stock' => ['required', 'integer', 'min:0'],
        ]);

        StoreProfile::updateOrCreate([], $validated);

        if ($request->boolean('apply_to_all')) {
            \App\Models\Product::query()->update(['min_stock_alert' => $validated['default_min_stock']]);
        }

        return back()->with('success', 'Pengaturan stok berhasil disimpan.');
    }
}
