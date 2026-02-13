<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'], // Opsional: Unik
            'category_id'   => ['required', 'exists:categories,id'], // Wajib ada di tabel categories
            'location_id'   => ['required', 'exists:locations,id'],  // Wajib ada di tabel locations
            
            // Batasi status agar user tidak input sembarangan lewat Inspect Element
            'status'        => ['required', 'in:normal,rusak,maintenance,disposed'], 
            
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Max 2MB
            'purchase_date' => ['nullable', 'date'],
            
            // Validasi Array Spesifikasi
            'specs_key'     => ['nullable', 'array'],
            'specs_key.*'   => ['nullable', 'string', 'max:255'], // Tiap item key harus string
            'specs_value'   => ['nullable', 'array'],
            'specs_value.*' => ['nullable', 'string', 'max:255'], // Tiap item value harus string
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori aset wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'location_id.required' => 'Lokasi aset wajib dipilih.',
            'location_id.exists'   => 'Lokasi tidak valid.',
            'status.in'            => 'Status tidak valid (Pilih: Normal, Rusak, Maintenance).',
            'image.max'            => 'Ukuran gambar maksimal 2MB.',
            'serial_number.unique' => 'Serial number sudah terdaftar.',
        ];
    }
}