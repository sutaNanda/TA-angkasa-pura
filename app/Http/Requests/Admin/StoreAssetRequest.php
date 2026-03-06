<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Hanya Admin yang boleh melakukan request ini.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Aturan validasi untuk CREATE aset baru.
     */
    public function rules(): array
    {
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
                // Unique check: serial number tidak boleh duplikat di seluruh tabel assets
                Rule::unique('assets', 'serial_number'),
            ],

            // --- Klasifikasi ---
            'category_id' => [
                'required',
                // Pastikan ID kategori benar-benar ada di DB
                Rule::exists('categories', 'id'),
            ],

            'location_id' => [
                'nullable', // Mengizinkan kosong (misal untuk Software/Lisensi)
                // Pastikan ID lokasi benar-benar ada di DB jika diisi
                Rule::exists('locations', 'id'),
            ],

            // --- Status: enum DB reference ---
            'status' => [
                'required',
                // Cocokkan tepat dengan nilai ENUM di tabel assets
                Rule::in(['normal', 'rusak', 'maintenance', 'hilang']),
            ],

            // --- Tanggal: tidak boleh tanggal masa depan ---
            'purchase_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            // --- Upload Gambar ---
            'images' => [
                'nullable',
                'array',
                'max:5', // Maksimal unggah 5 gambar sekaligus
            ],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2 MB per gambar
            ],

            // --- Spesifikasi (dikirim sebagai dua array paralel dari form) ---
            // Key: 'RAM', 'Processor', dll.
            'specs_key' => ['nullable', 'array'],
            'specs_key.*' => [
                'nullable',
                'string',
                'max:100',
                // Setiap key tidak boleh kosong jika ada (string yang isinya hanya spasi dianggap kosong)
            ],

            // Value: '8 GB', 'Intel i7', dll.
            'specs_value' => ['nullable', 'array'],
            'specs_value.*' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Pesan error yang ramah pengguna (Bahasa Indonesia).
     */
    public function messages(): array
    {
        return [
            // Nama
            'name.required'       => 'Nama aset wajib diisi.',
            'name.min'            => 'Nama aset minimal 3 karakter.',
            'name.max'            => 'Nama aset maksimal 255 karakter.',
            'name.string'         => 'Nama aset harus berupa teks.',

            // Serial Number
            'serial_number.max'        => 'Serial number maksimal 100 karakter.',
            'serial_number.unique'     => 'Serial number ini sudah terdaftar pada aset lain.',

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
            'images.max'   => 'Maksimal Anda hanya bisa mengunggah 5 gambar sekaligus.',
            'images.*.image'  => 'File yang diunggah harus berupa gambar.',
            'images.*.mimes'  => 'Format gambar yang diizinkan: JPG, JPEG, PNG, atau WebP.',
            'images.*.max'    => 'Ukuran gambar tidak boleh melebihi 2 MB.',

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