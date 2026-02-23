<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PasswordSetupController extends Controller
{
    /**
     * Tampilkan form ganti password (Dipaksa oleh middleware)
     */
    public function show()
    {
        return view('auth.password_setup');
    }

    /**
     * Proses update password baru
     */
    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
            'requires_password_reset' => false, // Matikan flag force reset
        ]);

        // Redirect sesuai role
        if ($user->role === 'teknisi') {
            return redirect()->route('technician.dashboard')->with('success', 'Password berhasil diatur. Selamat bekerja!');
        }

        return redirect('/')->with('success', 'Password berhasil diperbarui.');
    }
}
