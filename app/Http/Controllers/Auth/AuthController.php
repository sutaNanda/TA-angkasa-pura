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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
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
                // return redirect()->route('manager.dashboard');
            } elseif ($user->role === 'user') {
                return redirect()->route('user.tickets.index');
            }

            return redirect('/');
        }

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
