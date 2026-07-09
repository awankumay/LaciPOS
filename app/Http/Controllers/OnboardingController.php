<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingRequest;
use App\Models\StoreProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
                'timezone' => $profile->timezone,
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
            'timezone' => $request->validated('timezone'),
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        // Update or create (hanya 1 record)
        StoreProfile::updateOrCreate(
            [],
            $data
        );

        // Simpan security question & answer
        $user = Auth::user();
        if ($request->filled('security_question') && $request->filled('security_answer')) {
            $recoveryCodes = $this->generateRecoveryCodes();

            $user->update([
                'security_question' => $request->security_question,
                'security_answer' => strtolower($request->security_answer),
                'recovery_codes' => $recoveryCodes['hashed'],
            ]);

            return redirect()->route('onboarding.recovery-codes')
                ->with('recovery_codes', $recoveryCodes['raw']);
        }

        return redirect('/dashboard')
            ->with('success', 'Setup toko berhasil! Selamat berjualan.');
    }

    /**
     * Tampilkan halaman recovery codes setelah onboarding.
     */
    public function recoveryCodes()
    {
        if (!session('recovery_codes')) {
            return redirect('/dashboard');
        }

        return Inertia::render('Onboarding/RecoveryCodes', [
            'codes' => session('recovery_codes'),
        ]);
    }

    /**
     * Generate 10 recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        $raw = [];
        $hashed = [];

        for ($i = 0; $i < 10; $i++) {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $formatted = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            $raw[] = $formatted;
            $hashed[] = bcrypt($formatted);
        }

        return ['raw' => $raw, 'hashed' => $hashed];
    }
}
