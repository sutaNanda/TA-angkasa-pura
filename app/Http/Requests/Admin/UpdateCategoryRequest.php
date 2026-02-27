<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Semua admin yang terautentikasi boleh mengubah kategori.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk UPDATE kategori.
     *
     * Rule unique harus meng-ignore ID kategori yang sedang diedit agar
     * nama kategori itu sendiri tidak dianggap duplikat saat tidak diubah.
     */
    public function rules(): array
    {
        // Mendukung route model binding: route('/categories/{category}')
        // maupun route konvensional: route('/categories/{id}')
        $routeParam = $this->route('category') ?? $this->route('id');
        $categoryId = is_object($routeParam) ? $routeParam->getKey() : $routeParam;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                // Wajib mengandung minimal 1 huruf (mencegah input simbol/angka saja)
                'regex:/\p{L}/u',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],

            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Pesan error dalam Bahasa Indonesia (konsisten dengan StoreCategoryRequest).
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string'   => 'Nama kategori harus berupa teks.',
            'name.min'      => 'Nama kategori minimal 2 karakter.',
            'name.max'      => 'Nama kategori maksimal 255 karakter.',
            'name.regex'    => 'Nama kategori harus mengandung minimal satu huruf (tidak boleh angka atau simbol saja).',
            'name.unique'   => 'Nama kategori sudah digunakan oleh kategori lain.',

            'icon.string' => 'Format ikon tidak valid.',
            'icon.max'    => 'Nama ikon maksimal 255 karakter.',

            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max'    => 'Deskripsi maksimal 1000 karakter.',
        ];
    }

    /**
     * Nama atribut yang ramah untuk pesan error.
     */
    public function attributes(): array
    {
        return [
            'name'        => 'nama kategori',
            'icon'        => 'ikon',
            'description' => 'deskripsi',
        ];
    }
}
