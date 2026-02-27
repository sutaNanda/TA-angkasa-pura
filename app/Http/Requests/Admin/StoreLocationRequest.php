<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk CREATE lokasi baru.
     *
     * CATATAN: Kolom 'path' dan 'level' TIDAK divalidasi di sini —
     * keduanya di-generate otomatis oleh sistem saat penyimpanan.
     */
    public function rules(): array
    {
        return [
            // Kode unik lokasi (opsional, contoh: "GDG-A", "LT-2", "R-101")
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('locations', 'code'),
            ],

            // Nama lokasi dengan validasi ketat
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                // Aturan karakter: hanya boleh huruf, angka, spasi, dan simbol: - . ( ) /
                'regex:/^[a-zA-Z0-9\s\-\.\(\)\/]+$/',
                // Wajib mengandung minimal 1 huruf alfabet
                // (mencegah input berupa angka/simbol murni seperti "12345" atau "---")
                'regex:/[a-zA-Z]/',
            ],

            // Tipe lokasi sesuai enum sistem
            'type' => [
                'required',
                Rule::in(['building', 'floor', 'room', 'area']),
            ],

            // Lokasi induk (opsional, untuk struktur hierarki)
            'parent_id' => [
                'nullable',
                Rule::exists('locations', 'id'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Code
            'code.max'    => 'Kode lokasi maksimal 50 karakter.',
            'code.unique' => 'Kode lokasi sudah digunakan oleh lokasi lain.',

            // Name
            'name.required' => 'Nama lokasi wajib diisi.',
            'name.min'      => 'Nama lokasi minimal 3 karakter.',
            'name.max'      => 'Nama lokasi maksimal 255 karakter.',
            // Satu pesan untuk kedua rule regex (Laravel memakai key yang sama: 'name.regex')
            'name.regex'    => 'Nama lokasi tidak valid. Ketentuan: (1) Hanya boleh mengandung huruf, angka, spasi, dan simbol: - . ( ) /. (2) Wajib mengandung minimal 1 huruf alfabet — nama berupa angka saja seperti "12345" tidak diizinkan.',

            // Type
            'type.required' => 'Tipe lokasi wajib dipilih.',
            'type.in'       => 'Tipe lokasi tidak valid. Pilihan yang tersedia: Building, Floor, Room, atau Area.',

            // Parent
            'parent_id.exists' => 'Lokasi induk yang dipilih tidak ditemukan dalam sistem.',

            // Description
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code'        => 'kode lokasi',
            'name'        => 'nama lokasi',
            'type'        => 'tipe lokasi',
            'parent_id'   => 'lokasi induk',
            'description' => 'deskripsi',
        ];
    }
}