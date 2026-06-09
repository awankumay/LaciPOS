<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Guest routes (hanya bisa diakses saat belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Auth-only routes (tanpa onboarding guard — bisa diakses sebelum onboarding selesai)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Route onboarding TIDAK pakai middleware 'onboarding' (mencegah infinite redirect loop)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

// Protected routes (auth + onboarding harus selesai)
Route::middleware(['auth', 'onboarding'])->group(function () {
    // Route khusus owner
    Route::middleware('role:owner')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard/Index');
        })->name('dashboard');

        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('products', ProductController::class);

        // Route untuk reports, settings
        // akan ditambahkan di task masing-masing
    });

    // Route yang bisa diakses owner DAN cashier
    Route::middleware('role:owner,cashier')->group(function () {
        // Route POS akan ditambahkan di task T027
        // Route riwayat transaksi akan ditambahkan di task T049
    });
});

// Redirect root ke dashboard atau login
Route::get('/', function () {
    return redirect('/dashboard');
});
