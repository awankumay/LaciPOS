<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan (contoh: 'owner', 'cashier')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Jika user tidak login, redirect ke login
        if (!$user) {
            return redirect()->route('login');
        }

        // Cek apakah role user termasuk dalam daftar role yang diizinkan
        if (!in_array($user->role, $roles)) {
            // Jika cashier mencoba akses halaman owner, redirect ke POS
            if ($user->isCashier()) {
                return redirect('/pos')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            // Default: redirect ke dashboard
            return redirect('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
