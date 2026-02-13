<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <--- WAJIB IMPORT INI

class UpdateLocationRequest extends FormRequest
{
    /**
     * Tentukan apakah user berhak melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk Update Lokasi
     */
    public function rules(): array
    {
        // Ambil ID lokasi yang sedang diedit dari URL
        // Sesuaikan parameter route Anda (biasanya 'id' atau 'location')
        $id = $this->route('id') ?? $this->route('location'); 

        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                // Logika Unique Update:
                // 1. Abaikan data diri sendiri (ignore $id)
                // 2. Cek hanya data aktif (whereNull deleted_at)
                Rule::unique('locations', 'name')
                    ->ignore($id)
                    ->whereNull('deleted_at')
            ],
            
            // Batas deskripsi diperbesar jadi 1000 karakter
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan error kustom
     */
    public function messages(): array
    {
        return [
            'name.required'   => 'Nama lokasi wajib diisi.',
            'name.unique'     => 'Nama lokasi sudah digunakan oleh lokasi lain.',
            'description.max' => 'Deskripsi terlalu panjang (maksimal 1000 karakter).',
        ];
    }
}