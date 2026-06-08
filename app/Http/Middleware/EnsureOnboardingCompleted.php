<?php

namespace App\Http\Middleware;

use App\Models\StoreProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Redirect ke onboarding jika store_profile belum diisi.
     * Middleware ini hanya berlaku untuk user yang sudah login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user belum login, biarkan middleware auth yang handle
        if (!$request->user()) {
            return $next($request);
        }

        // Jika user sedang di halaman onboarding, jangan redirect (infinite loop)
        if ($request->routeIs('onboarding') || $request->routeIs('onboarding.store')) {
            return $next($request);
        }

        // Jika user sedang logout, jangan redirect
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // Cek apakah onboarding sudah selesai
        if (!StoreProfile::isOnboardingCompleted()) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
