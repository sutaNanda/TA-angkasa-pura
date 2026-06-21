<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil user
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Hitung Statistik Laporan Tiket
        // Total Laporan Keseluruhan
        $totalTickets = WorkOrder::where('reported_by', $user->id)->count();

        // Laporan Bulan Ini
        $ticketsThisMonth = WorkOrder::where('reported_by', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('user.profile.index', compact('user', 'totalTickets', 'ticketsThisMonth'));
    }

    /**
     * Update data profil (Nama, Email, Avatar)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        // Update Nama & Email
        $user->name = $request->name;
        $user->email = $request->email;

        // Update Avatar jika ada upload file baru
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan gambar baru ke storage/app/public/avatars
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path; 
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => [
                'required', 
                'confirmed', 
                \Illuminate\Validation\Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ], [
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.letters' => 'Password harus mengandung setidaknya satu huruf.',
            'new_password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
            'new_password.numbers' => 'Password harus mengandung setidaknya satu angka.',
            'new_password.symbols' => 'Password harus mengandung setidaknya satu simbol (@$!%*#?&).',
            'new_password.uncompromised' => 'Password ini terdeteksi bocor di data breach. Gunakan password lain.',
            'current_password.current_password' => 'Password saat ini salah.',
        ]);

        $user = Auth::user();

        // Menyimpan password baru (hashing)
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah dengan standar keamanan tinggi!');
    }
}
