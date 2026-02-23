<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user login DAN requires_password_reset = true
        if ($user && $user->requires_password_reset) {
            
            // Izinkan akses ke route setup password & logout agar tidak redirect loop
            if ($request->routeIs('password.setup') || $request->routeIs('password.update') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('password.setup')->with('warning', 'Anda wajib mengganti password default sebelum melanjutkan.');
        }

        return $next($request);
    }
}
