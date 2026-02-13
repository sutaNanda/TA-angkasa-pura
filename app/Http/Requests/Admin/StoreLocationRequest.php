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

    public function rules(): array
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                
                // 1. CEGAH SCRIPT JAHAT
                // Menolak input yang mengandung karakter < atau >
                'not_regex:/[<>]/', 

                // 2. CEGAH ANGKA SAJA / SIMBOL SAJA
                // Mewajibkan input mengandung minimal satu huruf (a-z atau A-Z)
                // Jadi "12345" (Gagal), "@#$%" (Gagal), "Gudang 1" (Sukses)
                'regex:/[a-zA-Z]/', 

                // Validasi Unique
                Rule::unique('locations', 'name')->whereNull('deleted_at')
            ],
            
            'parent_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama lokasi wajib diisi.',
            'name.unique'    => 'Nama lokasi sudah digunakan.',
            
            // Pesan Error Khusus Regex
            'name.not_regex' => 'Nama lokasi tidak boleh mengandung simbol.',
            'name.regex'     => 'Nama lokasi harus mengandung setidaknya satu huruf.',
            
            'parent_id.exists' => 'Induk lokasi (Parent) tidak valid.',
            'description.max'  => 'Deskripsi terlalu panjang (maksimal 1000 karakter).',
        ];
    }
}