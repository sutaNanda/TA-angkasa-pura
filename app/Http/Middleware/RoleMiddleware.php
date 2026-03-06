<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has the required role
        if (!in_array($user->role, $roles)) {
            // Redirect based on role if they try to access unauthorized area
            if ($user->role === 'admin' || $user->role === 'manajer') {
                return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            } elseif ($user->role === 'teknisi') {
                return redirect()->route('technician.dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            } elseif ($user->role === 'user') {
                return redirect()->route('user.tickets.index')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            }

            return redirect('/')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
