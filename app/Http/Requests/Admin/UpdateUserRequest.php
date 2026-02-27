<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk UPDATE user oleh Admin.
     *
     * TIGA PERBEDAAN KRUSIAL dibanding StoreUserRequest:
     * 1. `email` — unique() meng-ignore ID user yang sedang diedit.
     * 2. `password` — nullable; hanya divalidasi kompleksitasnya JIKA diisi.
     * 3. `password_confirmation` — hanya relevan jika password diisi.
     */
    public function rules(): array
    {
        // Resolve route parameter — support model binding ({user}) & ID konvensional ({id})
        $routeParam = $this->route('user') ?? $this->route('id');
        $userId = is_object($routeParam) ? $routeParam->getKey() : $routeParam;

        return [
            // ─── Identitas ──────────────────────────────────────────────────────────
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z\s\.\,\'\-]+$/',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                // KRUSIAL #1: Abaikan email milik user yang sedang diedit
                Rule::unique('users', 'email')->ignore($userId),
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
                'max:2048',
            ],

            // ─── Password (OPSIONAL saat Update) ────────────────────────────────────
            // KRUSIAL #2: nullable — field password boleh dikosongkan
            // KRUSIAL #3: 'confirmed' hanya akan berjalan jika password diisi,
            //             karena seluruh rule di sini di-skip jika nilai null/kosong.
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
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
            'email.unique'   => 'Alamat email ini sudah digunakan oleh user lain.',

            // Role
            'role.required' => 'Peran (role) user wajib dipilih.',
            'role.in'       => 'Peran tidak valid. Pilihan: Admin, Teknisi, Manajer, atau User.',

            // Division
            'division.in' => 'Divisi yang dipilih tidak valid.',

            // Avatar
            'avatar.image' => 'File yang diunggah harus berupa gambar.',
            'avatar.mimes' => 'Format gambar yang diizinkan: JPEG, PNG, JPG, atau WebP.',
            'avatar.max'   => 'Ukuran gambar tidak boleh melebihi 2 MB.',

            // Password — opsional saat update, tapi jika diisi harus kuat
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
