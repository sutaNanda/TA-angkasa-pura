<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar (Kecuali user yang sedang login)
        $query = User::with('shift')->where('id', '!=', auth()->id());

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Ambil data dengan Pagination (10 per halaman)
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        $shifts = Shift::orderBy('id')->get();

        return view('admin.users.index', compact('users', 'shifts'));
    }

    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,teknisi,manajer,user',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        // Generate Password Otomatis utk role dengan format (Min 12, Ltr, Num, Symbol)
        $password = \Illuminate\Support\Str::password(12, true, true, true, false);
        
        // Simpan Data
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'shift_id' => $request->shift_id,
            'requires_password_reset' => true,
        ]);

        // Kirim Email Notifikasi ke Password
        try {
            $user->notify(new \App\Notifications\UserCredentialsNotification($password));
            return back()->with('success', 'User berhasil ditambahkan. Password dikirim ke email ' . $request->email);
        } catch (\Exception $e) {
            return back()->with('success', 'User dibuat tapi email gagal. Password sementara: ' . $password);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,teknisi,manajer,user',
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        // Update Data Dasar
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'shift_id' => $request->shift_id,
        ];

        // Jika password diisi, update password baru. Jika kosong, biarkan password lama.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Cegah hapus diri sendiri
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete(); // Soft Delete

        return back()->with('success', 'User berhasil dinonaktifkan.');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}