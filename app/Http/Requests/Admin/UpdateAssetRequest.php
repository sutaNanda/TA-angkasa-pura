<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil ID dari route parameter (misal: /assets/{id})
        $id = $this->route('id') ?? $this->route('asset');

        return [
            'name'          => ['required', 'string', 'max:255'],
            
            // Ignore ID saat cek unique serial number
            'serial_number' => [
                'nullable', 
                'string', 
                'max:100', 
                Rule::unique('assets', 'serial_number')->ignore($id)
            ],
            
            'category_id'   => ['required', 'exists:categories,id'],
            'location_id'   => ['required', 'exists:locations,id'],
            'status'        => ['required', 'in:normal,rusak,maintenance,disposed'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'purchase_date' => ['nullable', 'date'],
            
            'specs_key'     => ['nullable', 'array'],
            'specs_key.*'   => ['nullable', 'string', 'max:255'],
            'specs_value'   => ['nullable', 'array'],
            'specs_value.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}