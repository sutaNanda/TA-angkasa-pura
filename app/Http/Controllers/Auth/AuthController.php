<?php

namespace App\Http\Controllers\Auth; // <--- Namespace Berubah

use App\Http\Controllers\Controller; // <--- Import Controller Utama
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Tampilkan Form Login
    public function showLoginForm()
    {
        // Pastikan file view ada di: resources/views/auth/login.blade.php
        return view('auth.login');
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke route login setelah logout
        return redirect()->route('login');
    }
}
