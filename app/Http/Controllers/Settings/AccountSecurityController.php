<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AccountSecurityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Settings/AccountSecurity', [
            'security_question' => $user->security_question,
            'hasSecuritySetup' => $user->hasSecuritySetup(),
            'recovery_codes_count' => count($user->recovery_codes ?? []),
            'new_recovery_codes' => session()->pull('new_recovery_codes'),
        ]);
    }

    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'security_question' => ['required', 'string', 'max:255'],
            'security_answer' => ['required', 'string', 'max:255'],
            'current_password' => ['required', 'string', 'current_password'],
        ]);

        $user->update([
            'security_question' => $request->security_question,
            'security_answer' => strtolower($request->security_answer),
        ]);

        return back()->with('success', 'Pertanyaan keamanan berhasil diperbarui.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => $request->new_password,
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function generateCodes(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        $raw = [];
        $hashed = [];

        for ($i = 0; $i < 10; $i++) {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $formatted = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            $raw[] = $formatted;
            $hashed[] = bcrypt($formatted);
        }

        $user->update(['recovery_codes' => $hashed]);

        session()->flash('new_recovery_codes', $raw);

        return back()->with('success', 'Kode pemulihan baru berhasil dibuat.');
    }
}
