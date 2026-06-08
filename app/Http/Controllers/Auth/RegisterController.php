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
     */
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Proses registrasi user baru.
     * User pertama yang register otomatis menjadi owner.
     */
    public function store(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => 'owner', // User yang register sendiri selalu owner
        ]);

        Auth::login($user);

        // Redirect ke onboarding jika belum setup toko
        // Untuk saat ini redirect ke dashboard, akan diupdate di T014
        return redirect()->intended('/dashboard');
    }
}
