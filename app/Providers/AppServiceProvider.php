<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aktifkan WAL mode untuk SQLite (meningkatkan reliabilitas)
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA journal_mode=WAL;');
        }

        if (class_exists(\App\Models\Order::class)) {
            \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        }
    }
}
