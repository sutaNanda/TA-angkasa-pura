<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk CREATE user baru oleh Admin.
     */
    public function rules(): array
    {
        return [
            // ─── Identitas ──────────────────────────────────────────────────────────
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                // Hanya izinkan: huruf (termasuk aksen A-z), spasi, titik, koma,
                // kutip tunggal (untuk nama seperti O'Brien), dan strip.
                // Larang: angka, @, #, $, dan simbol lainnya.
                'regex:/^[a-zA-Z\s\.\,\'\-]+$/',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc,dns', // Validasi format RFC + cek MX record DNS
                'max:255',
                Rule::unique('users', 'email'),
            ],

            // ─── Peran & Divisi ─────────────────────────────────────────────────────
            'role' => [
                'required',
                Rule::in(['admin', 'teknisi', 'manajer', 'user']),
            ],

            'division' => [
                'nullable',
                'string',
                Rule::in(['IT', 'HR & GA', 'Finance', 'Operations', 'Commercial', 'Technical', 'Security']),
            ],

            // ─── Pengaturan Akun ────────────────────────────────────────────────────
            'requires_password_reset' => [
                'nullable',
                'boolean',
            ],

            // ─── Avatar ─────────────────────────────────────────────────────────────
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048', // 2 MB
            ],

            // ─── Password (WAJIB saat Create) ───────────────────────────────────────
            'password' => [
                'required',
                'confirmed', // Mencocokkan dengan field 'password_confirmation'
                Password::min(8)
                    ->letters()      // Harus ada huruf
                    ->mixedCase()    // Harus ada huruf besar DAN kecil
                    ->numbers()      // Harus ada angka
                    ->symbols()      // Harus ada simbol (!, @, #, dll.)
                    ->uncompromised(), // Cek ke database HaveIBeenPwned (opsional, butuh internet)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Nama
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min'      => 'Nama lengkap minimal 3 karakter.',
            'name.max'      => 'Nama lengkap maksimal 255 karakter.',
            'name.regex'    => 'Format nama tidak valid. Nama hanya boleh mengandung huruf, spasi, titik (.), koma (,), tanda kutip (\'), dan strip (-). Angka dan simbol lain tidak diizinkan.',

            // Email
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
            'email.max'      => 'Alamat email maksimal 255 karakter.',
            'email.unique'   => 'Alamat email ini sudah terdaftar. Gunakan email lain.',

            // Role
            'role.required' => 'Peran (role) user wajib dipilih.',
            'role.in'       => 'Peran tidak valid. Pilihan: Admin, Teknisi, Manajer, atau User.',

            // Division
            'division.in' => 'Divisi yang dipilih tidak valid.',

            // Avatar
            'avatar.image' => 'File yang diunggah harus berupa gambar.',
            'avatar.mimes' => 'Format gambar yang diizinkan: JPEG, PNG, JPG, atau WebP.',
            'avatar.max'   => 'Ukuran gambar tidak boleh melebihi 2 MB.',

            // Password — berikan panduan eksplisit agar user paham apa yang kurang
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok. Pastikan kedua kolom password sama.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.mixed'     => 'Password harus mengandung minimal 1 huruf besar dan 1 huruf kecil.',
            'password.letters'   => 'Password harus mengandung minimal 1 huruf.',
            'password.numbers'   => 'Password harus mengandung minimal 1 angka.',
            'password.symbols'   => 'Password harus mengandung minimal 1 simbol (contoh: ! @ # $ % ^).',
            'password.uncompromised' => 'Password ini terdeteksi pernah bocor dalam data breach publik. Gunakan password yang berbeda.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                    => 'nama lengkap',
            'email'                   => 'alamat email',
            'role'                    => 'peran user',
            'division'                => 'divisi',
            'requires_password_reset' => 'wajib reset password',
            'avatar'                  => 'foto profil',
            'password'                => 'password',
        ];
    }
}
