<?php

namespace App\Http\Controllers;

use App\Models\StoreProfile;
use Illuminate\Http\Request;
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
            'existingProfile' => $profile,
        ]);
    }

    /**
     * Simpan data onboarding (dipanggil di step terakhir).
     * Endpoint ini akan digunakan di T013.
     */
    public function store(Request $request)
    {
        // Akan diimplementasikan lengkap di T013
    }
}
