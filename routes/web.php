<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Guest routes (hanya bisa diakses saat belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated routes (harus login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');
});

// Redirect root ke dashboard atau login
Route::get('/', function () {
    return redirect('/dashboard');
});
