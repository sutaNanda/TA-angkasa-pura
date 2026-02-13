<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
// IMPORT REQUEST DARI FOLDER ADMIN
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Requests\Admin\UpdateLocationRequest;
use Illuminate\Http\Request; // Masih dipakai untuk getTree (karena tidak ada input form)

class LocationController extends Controller
{
    /**
     * Mengembalikan struktur Tree (Parent -> Children)
     */
    public function getTree()
    {
        $locations = Location::whereNull('parent_id')
                    ->with('children.children')
                    ->orderBy('name', 'asc')
                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    /**
     * Simpan Lokasi Baru
     * Menggunakan: StoreLocationRequest (Admin)
     */
    public function store(StoreLocationRequest $request)
    {
        // Validasi otomatis berjalan di background.
        // Gunakan $request->validated() untuk mengambil data yang sudah bersih/aman.
        $location = Location::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil dibuat',
            'data' => $location
        ]);
    }

    /**
     * Update Lokasi
     * Menggunakan: UpdateLocationRequest (Admin)
     */
    public function update(UpdateLocationRequest $request, $id)
    {
        $location = Location::findOrFail($id);

        // Hanya kolom yang didefinisikan di rules() UpdateLocationRequest yang akan di-update
        // (name & description). parent_id aman tidak akan berubah.
        $location->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil diperbarui',
            'data' => $location
        ]);
    }

    /**
     * Hapus Lokasi
     * Logika bisnis validasi hapus tetap di controller (karena ini cek relasi DB, bukan validasi input form)
     */
    public function destroy($id)
    {
        $location = Location::findOrFail($id);

        // VALIDASI 1: Cek Sub-lokasi
        if ($location->children()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal! Lokasi ini memiliki sub-lokasi aktif. Hapus sub-lokasi dulu.'
            ], 422);
        }

        // VALIDASI 2: Cek Aset
        if ($location->assets()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal! Masih ada aset terdaftar di lokasi ini.'
            ], 422);
        }

        $location->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil dihapus (arsip)'
        ]);
    }
}