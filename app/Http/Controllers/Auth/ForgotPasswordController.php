<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan form input email owner.
     */
    public function index()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Verifikasi email owner, lalu redirect ke form reset.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('role', 'owner')
            ->where('email', $request->email)
            ->first();

        if (!$user || !$user->hasSecuritySetup()) {
            // Dummy bcrypt untuk menyamakan response time
            if (!$user) {
                Hash::check('dummy', '$2y$10$' . str_repeat('0', 53));
            }
            return back()->withErrors([
                'email' => 'Email tidak ditemukan atau keamanan akun belum diatur.',
            ]);
        }

        session()->put('reset_email', $user->email);
        session()->put('reset_question', $user->security_question);
        session()->put('reset_expires_at', now()->addMinutes(30));

        return redirect()->route('forgot-password.reset');
    }

    /**
     * Tampilkan form reset password (data dari session).
     */
    public function showResetForm()
    {
        if (!session('reset_email') || !session('reset_question')) {
            return redirect()->route('forgot-password');
        }

        if (now()->greaterThan(session('reset_expires_at'))) {
            session()->forget(['reset_email', 'reset_question', 'reset_expires_at']);
            return redirect()->route('forgot-password');
        }

        return Inertia::render('Auth/ResetPassword', [
            'email' => session('reset_email'),
            'security_question' => session('reset_question'),
        ]);
    }

    /**
     * Verifikasi jawaban atau recovery code, lalu reset password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'security_answer' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::where('role', 'owner')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        if (now()->greaterThan(session('reset_expires_at'))) {
            session()->forget(['reset_email', 'reset_question', 'reset_expires_at']);
            return redirect()->route('forgot-password');
        }

        if ($request->filled('security_answer')) {
            if (!Hash::check(strtolower($request->security_answer), $user->security_answer)) {
                return back()->withErrors(['security_answer' => 'Jawaban keamanan salah.']);
            }

            $user->update([
                'password' => $request->password,
            ]);

            session()->forget(['reset_email', 'reset_question', 'reset_expires_at']);

            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        if ($request->filled('recovery_code')) {
            $codes = $user->recovery_codes ?? [];

            $matchedIndex = null;
            foreach ($codes as $index => $hashedCode) {
                if (Hash::check(strtoupper($request->recovery_code), $hashedCode)) {
                    $matchedIndex = $index;
                    break;
                }
            }

            if ($matchedIndex === null) {
                return back()->withErrors(['recovery_code' => 'Kode pemulihan tidak valid atau sudah digunakan.']);
            }

            unset($codes[$matchedIndex]);
            $codes = array_values($codes);

            $user->update([
                'password' => $request->password,
                'recovery_codes' => $codes,
            ]);

            session()->forget(['reset_email', 'reset_question', 'reset_expires_at']);

            return redirect()->route('login')
                ->with('success', 'Password berhasil direset menggunakan kode pemulihan. Silakan login dengan password baru.');
        }

        return back()->withErrors(['security_answer' => 'Jawaban keamanan atau kode pemulihan wajib diisi.']);
    }
}
