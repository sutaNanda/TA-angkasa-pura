<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar & Search
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        // 2. Ambil data dengan Pagination (10 per halaman)
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,teknisi,manajer', // User removed as per request
            // Password tidak perlu divalidasi karena auto-generate
        ]);

        // Generate Password Otomatis untuk SEMUA Role
        $password = \Illuminate\Support\Str::random(10);
        
        // Simpan Data
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'requires_password_reset' => true, // Paksa reset password
        ]);

        // Kirim Email Notifikasi ke Password
        try {
            $user->notify(new \App\Notifications\UserCredentialsNotification($password));
            return back()->with('success', 'User berhasil ditambahkan. Password dikirim ke email ' . $request->email);
        } catch (\Exception $e) {
            // Fallback jika email gagal
            return back()->with('success', 'User dibuat tapi email gagal. Password sementara: ' . $password);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)], // Email boleh sama kalau punya sendiri
            'role' => 'required|in:admin,teknisi,manajer,user',
            'password' => 'nullable|string|min:6', // Password boleh kosong
        ]);

        // Update Data Dasar
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
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
}