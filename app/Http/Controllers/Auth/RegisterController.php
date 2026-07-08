<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Tampilkan halaman register.
     * Hanya bisa diakses jika belum ada owner (first-user registration).
     */
    public function create()
    {
        if (User::where('role', 'owner')->exists()) {
            return redirect()->route('login')->with('error', 'Registrasi hanya untuk pemilik pertama. Hubungi pemilik untuk menambahkan kasir.');
        }

        return Inertia::render('Auth/Register', [
            'isFirstSetup' => true,
        ]);
    }

    /**
     * Proses registrasi user baru.
     * User pertama yang register otomatis menjadi owner.
     */
    public function store(RegisterRequest $request)
    {
        if (User::where('role', 'owner')->exists()) {
            return redirect()->route('login')->with('error', 'Registrasi hanya untuk pemilik pertama.');
        }

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => 'owner',
        ]);

        Auth::login($user);

        return redirect()->intended('/onboarding');
    }
}
