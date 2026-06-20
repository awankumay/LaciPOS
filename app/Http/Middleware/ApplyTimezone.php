<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $profile = \App\Models\StoreProfile::getProfile();
            if ($profile && $profile->timezone) {
                date_default_timezone_set($profile->timezone);
                config(['app.timezone' => $profile->timezone]);
            }
        } catch (\Exception $e) {
            // Abaikan jika tabel belum ada saat migrasi awal
        }

        return $next($request);
    }
}
