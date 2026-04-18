<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Hitung Statistik Nyata
        // Total Selesai
        $completedTotal = WorkOrder::where('technician_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // Selesai Bulan Ini
        $completedThisMonth = WorkOrder::where('technician_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        return view('technician.profile.index', compact('user', 'completedTotal', 'completedThisMonth'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        // Update Nama & Email
        $user->name = $request->name;
        $user->email = $request->email;

        // Update Avatar jika ada upload
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika bukan default
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan yang baru
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path; // Pastikan kolom 'avatar' ada di tabel users (atau gunakan kolom 'image')
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

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

        $user = auth()->user();

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah dengan standar keamanan tinggi!');
    }
}
