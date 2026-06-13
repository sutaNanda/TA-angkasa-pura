<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TechnicianGroup;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar (Kecuali user yang sedang login)
        $query = User::with(['group', 'department'])->where('id', '!=', auth()->id());

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // 2. Ambil data dengan Pagination (10 per halaman)
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        // Kirim daftar grup dan departemen untuk dropdown form tambah/edit user
        $groups = TechnicianGroup::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'groups', 'departments'));
    }

    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('users')->whereNull('deleted_at')
            ],
            'role'  => 'required|in:admin,teknisi,manajer,user',
            // technician_group_id hanya relevan untuk role teknisi
            'technician_group_id' => [
                'nullable',
                Rule::exists('technician_groups', 'id'),
                Rule::requiredIf(fn() => $request->role === 'teknisi'),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id'),
                Rule::requiredIf(fn() => $request->role === 'user'),
                Rule::unique('users')->where(fn($query) => $query->where('role', 'user')->whereNull('deleted_at')),
            ],
        ]);

        // Generate Password Otomatis (Min 12, Huruf, Angka, Simbol)
        $password = \Illuminate\Support\Str::password(12, true, true, true, false);

        // Cek apakah ada user (termasuk yang sudah dihapus/soft delete)
        $user = User::withTrashed()->where('email', $request->email)->first();

        if ($user) {
            // Restore dan Update Data
            $user->restore(); // Mengubah deleted_at menjadi null
            $user->update([
                'name'                    => $request->name,
                'password'                => Hash::make($password),
                'role'                    => $request->role,
                'technician_group_id'     => $request->role === 'teknisi' ? $request->technician_group_id : null,
                'department_id'           => $request->role === 'user' ? $request->department_id : null,
                'requires_password_reset' => true,
            ]);
        } else {
            // Simpan Data Baru
            $user = User::create([
                'name'                    => $request->name,
                'email'                   => $request->email,
                'password'                => Hash::make($password),
                'role'                    => $request->role,
                'technician_group_id'     => $request->role === 'teknisi' ? $request->technician_group_id : null,
                'department_id'           => $request->role === 'user' ? $request->department_id : null,
                'requires_password_reset' => true,
            ]);
        }

        // Kirim Email Notifikasi berisi Password
        try {
            $user->notify(new \App\Notifications\UserCredentialsNotification($password));
            return back()->with('success', 'User berhasil ditambahkan/diaktifkan kembali. Password dikirim ke email ' . $request->email);
        } catch (\Exception $e) {
            return back()->with('success', 'User berhasil dibuat/diaktifkan, tapi email gagal dikirim. Password sementara: ' . $password);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'     => 'required|in:admin,teknisi,manajer,user',
            'technician_group_id' => 'nullable|exists:technician_groups,id',
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id'),
                Rule::requiredIf(fn() => $request->role === 'user'),
                Rule::unique('users')->where(fn($query) => $query->where('role', 'user'))->ignore($user->id),
            ],
        ]);

        // Update Data Dasar
        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
            // Set grup hanya untuk role teknisi; peran lain selalu null
            'technician_group_id' => $request->role === 'teknisi' ? $request->technician_group_id : null,
            'department_id' => $request->role === 'user' ? $request->department_id : null,
        ];

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
            $user->avatar = \App\Services\ImageCompressorService::upload($request->file('avatar'), 'avatars');
        }

        $user->save();

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}