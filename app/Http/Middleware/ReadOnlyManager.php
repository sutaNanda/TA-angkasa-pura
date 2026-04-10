<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReadOnlyManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika yang login adalah manajer, batasi metode request-nya
        if (auth()->check() && auth()->user()->role === 'manajer') {
            
            // Allow methods GET, HEAD, OPTIONS
            if (!$request->isMethodSafe()) {
                
                // Allow Manager to update their own profile
                if ($request->routeIs('admin.profile.update') || $request->routeIs('profile.update')) {
                    return $next($request);
                }
                
                // Jika request adalah AJAX / JSON
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Akses ditolak. Manajer hanya dapat melihat data (Read-Only).'
                    ], 403);
                }

                // Redirect kembali ke halaman sebelumnya dengan pesan error
                return redirect()->back()->with('error', 'Akses ditolak. Manajer hanya dapat melihat data (Read-Only).');
            }
        }

        return $next($request);
    }
}
