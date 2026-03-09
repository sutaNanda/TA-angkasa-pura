<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Hanya Admin yang boleh melakukan request ini.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Aturan validasi untuk UPDATE aset.
     *
     * PENTING: Unique rule harus meng-ignore ID aset yang sedang di-update
     * agar serial number aset itu sendiri tidak dianggap duplikat.
     */
    public function rules(): array
    {
        // Ambil ID dari route parameter (support kedua konvensi: {asset} atau {id})
        // Jika model binding digunakan, $this->route('asset') mengembalikan object Model
        $routeParam = $this->route('asset') ?? $this->route('id');
        $assetId = is_object($routeParam) ? $routeParam->getKey() : $routeParam;

        return [
            // --- Informasi Utama ---
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'serial_number' => [
                'nullable',
                'string',
                'max:100',
                // Ignore ID aset saat ini agar tidak bertabrakan dengan dirinya sendiri
                Rule::unique('assets', 'serial_number')->ignore($assetId),
            ],

            // --- Klasifikasi ---
            'category_id' => [
                'required',
                Rule::exists('categories', 'id'),
            ],

            'location_id' => [
                'nullable',
                Rule::exists('locations', 'id'),
            ],

            // --- Induk Aset (untuk Software/Lisensi) ---
            'parent_asset_id' => [
                'nullable',
                Rule::exists('assets', 'id'),
            ],

            // --- Status (termasuk status software) ---
            'status' => [
                'required',
                Rule::in(['normal', 'rusak', 'maintenance', 'hilang', 'aktif', 'kedaluwarsa', 'ditangguhkan']),
            ],

            // --- Tanggal ---
            'purchase_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            // --- Upload Gambar (opsional saat update) ---
            'images' => [
                'nullable',
                'array',
                'max:5',
            ],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // --- Gambar yang dipertahankan saat edit ---
            'kept_images' => ['nullable', 'array'],
            'kept_images.*' => ['string'],

            // --- Spesifikasi ---
            'specs_key'   => ['nullable', 'array'],
            'specs_key.*' => ['nullable', 'string', 'max:100'],

            'specs_value'   => ['nullable', 'array'],
            'specs_value.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan error yang ramah pengguna (Bahasa Indonesia).
     * Sama persis dengan StoreAssetRequest untuk konsistensi UX.
     */
    public function messages(): array
    {
        return [
            // Nama
            'name.required' => 'Nama aset wajib diisi.',
            'name.min'      => 'Nama aset minimal 3 karakter.',
            'name.max'      => 'Nama aset maksimal 255 karakter.',

            // Serial Number
            'serial_number.max'    => 'Serial number maksimal 100 karakter.',
            'serial_number.unique' => 'Serial number ini sudah digunakan oleh aset lain.',

            // Kategori & Lokasi
            'category_id.required' => 'Kategori aset wajib dipilih.',
            'category_id.exists'   => 'Kategori yang dipilih tidak ditemukan dalam sistem.',
            'location_id.required' => 'Lokasi aset wajib dipilih.',
            'location_id.exists'   => 'Lokasi yang dipilih tidak ditemukan dalam sistem.',

            // Status
            'status.required' => 'Status aset wajib dipilih.',
            'status.in'       => 'Status tidak valid. Pilihan: Normal, Rusak, Maintenance, atau Hilang.',

            // Tanggal
            'purchase_date.date'            => 'Format tanggal pembelian tidak valid.',
            'purchase_date.before_or_equal' => 'Tanggal pembelian tidak boleh melebihi hari ini.',

            // Gambar
            'images.max'  => 'Maksimal Anda hanya bisa mengunggah 5 gambar sekaligus.',
            'images.*.image' => 'File yang diunggah harus berupa gambar.',
            'images.*.mimes' => 'Format gambar yang diizinkan: JPG, JPEG, PNG, atau WebP.',
            'images.*.max'   => 'Ukuran gambar tidak boleh melebihi 2 MB.',

            // Spesifikasi
            'specs_key.*.max'   => 'Nama spesifikasi (#:position) terlalu panjang, maksimal 100 karakter.',
            'specs_value.*.max' => 'Nilai spesifikasi (#:position) terlalu panjang, maksimal 500 karakter.',
        ];
    }

    /**
     * Custom attribute names untuk pesan error yang lebih deskriptif.
     */
    public function attributes(): array
    {
        return [
            'name'          => 'nama aset',
            'serial_number' => 'serial number',
            'category_id'   => 'kategori',
            'location_id'   => 'lokasi',
            'status'        => 'status aset',
            'purchase_date' => 'tanggal pembelian',
            'image'         => 'gambar aset',
        ];
    }
}