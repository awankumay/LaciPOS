<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingRequest;
use App\Models\StoreProfile;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    /**
     * Tampilkan halaman onboarding wizard.
     */
    public function index()
    {
        $profile = StoreProfile::getProfile();

        return Inertia::render('Onboarding/Index', [
            'existingProfile' => $profile ? [
                'store_name' => $profile->store_name,
                'address' => $profile->address,
                'phone' => $profile->phone,
                'logo_url' => $profile->logo_url,
                'receipt_footer' => $profile->receipt_footer,
            ] : null,
        ]);
    }

    /**
     * Simpan data onboarding ke database.
     * Upload logo ke storage jika ada.
     */
    public function store(OnboardingRequest $request)
    {
        $data = [
            'store_name' => $request->validated('store_name'),
            'address' => $request->validated('address'),
            'phone' => $request->validated('phone'),
            'receipt_footer' => $request->validated('receipt_footer'),
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        // Update or create (hanya 1 record)
        StoreProfile::updateOrCreate(
            [], // Tidak ada where condition, selalu row pertama
            $data
        );

        return redirect('/dashboard')
            ->with('success', 'Setup toko berhasil! Selamat berjualan.');
    }
}
