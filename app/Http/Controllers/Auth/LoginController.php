<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     * Jika belum ada owner, redirect ke halaman register.
     */
    public function create()
    {
        if (!User::where('role', 'owner')->exists()) {
            return redirect()->route('register');
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Proses login.
     */
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Cek apakah user aktif
        if (!Auth::attempt(array_merge($credentials, ['is_active' => true]))) {
            return back()->withErrors([
                'email' => 'Email atau password salah, atau akun Anda telah dinonaktifkan.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Proses logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
