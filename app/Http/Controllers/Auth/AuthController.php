<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Tampilkan Form Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Key untuk rate limiter (berdasarkan email dan IP)
        $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($request->input('email')).'|'.$request->ip());

        // Maksimal 5 percobaan, block selama 1 menit (60 detik)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            
            // Catat log jika terkena brute force
            AuditLog::record('login_blocked', 'Authentication', "IP {$request->ip()} diblokir sementara karena terlalu banyak percobaan login untuk email: {$request->email}");

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey); // Hapus limit jika berhasil
            
            $request->session()->regenerate();

            $user = Auth::user();

            // Catat log aktivitas login
            AuditLog::record('login', 'Authentication', "User {$user->name} berhasil masuk ke sistem");

            // Redirect sesuai Role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'teknisi') {
                return redirect()->route('technician.dashboard');
            } elseif ($user->role === 'manajer') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'user') {
                return redirect()->route('user.tickets.index');
            }

            return redirect('/');
        }

        // Tambah hit ke rate limiter jika gagal
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        // Catat log gagal masuk
        AuditLog::record('login_failed', 'Authentication', "Gagal login dengan email: {$request->email} (Password salah / tidak terdaftar)");

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Catat log aktivitas logout sebelum session dihapus
        if ($user) {
            AuditLog::record('logout', 'Authentication', "User {$user->name} keluar dari sistem");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
