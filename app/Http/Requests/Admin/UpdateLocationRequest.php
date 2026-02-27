<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk UPDATE lokasi.
     *
     * Fitur khusus:
     * 1. Rule unique pada 'code' mengabaikan ID lokasi yang sedang diedit.
     * 2. Closure Rule pada 'parent_id' mencegah lokasi menjadi induk bagi dirinya sendiri
     *    (yang akan menyebabkan infinite loop pada traversal pohon hierarki).
     */
    public function rules(): array
    {
        // Resolve route parameter — support model binding & ID konvensional
        $routeParam = $this->route('location') ?? $this->route('id');
        $locationId = is_object($routeParam) ? $routeParam->getKey() : $routeParam;

        return [
            'code' => [
                'nullable',
                'string',
                'max:50',
                // Abaikan kode milik lokasi yang sedang diedit
                Rule::unique('locations', 'code')->ignore($locationId),
            ],

            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\.\(\)\/]+$/',
                'regex:/[a-zA-Z]/',
            ],

            'type' => [
                'required',
                Rule::in(['building', 'floor', 'room', 'area']),
            ],

            'parent_id' => [
                'nullable',
                Rule::exists('locations', 'id'),

                // ─── CUSTOM CLOSURE RULE: Anti Self-Referencing ───────────────────
                // Mencegah lokasi menjadi induk bagi dirinya sendiri.
                // Contoh skenario: User mengedit Gedung A dan memilih Gedung A
                // sebagai parent-nya sendiri → akan membentuk loop tak terbatas
                // pada fungsi getChildren(), buildTree(), atau path generator.
                function (string $attribute, mixed $value, Closure $fail) use ($locationId): void {
                    if ((int) $value === (int) $locationId) {
                        $fail('Lokasi tidak bisa menjadi induk bagi dirinya sendiri.');
                    }
                },
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
            'name.regex'    => 'Nama lokasi tidak valid. Ketentuan: (1) Hanya boleh mengandung huruf, angka, spasi, dan simbol: - . ( ) /. (2) Wajib mengandung minimal 1 huruf alfabet — nama berupa angka saja seperti "12345" tidak diizinkan.',

            // Type
            'type.required' => 'Tipe lokasi wajib dipilih.',
            'type.in'       => 'Tipe lokasi tidak valid. Pilihan yang tersedia: Building, Floor, Room, atau Area.',

            // Parent
            'parent_id.exists' => 'Lokasi induk yang dipilih tidak ditemukan dalam sistem.',
            // Pesan Closure Rule ditentukan langsung di dalam Closure via $fail()

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