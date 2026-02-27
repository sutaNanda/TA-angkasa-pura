<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Http\Requests\Admin\StoreAssetRequest;
use App\Http\Requests\Admin\UpdateAssetRequest;
use App\Traits\ImageUploadTrait;

class AssetController extends Controller
{
    use ImageUploadTrait;
    public function index()
    {
        $locations = Location::whereNull('parent_id')->with('children')->get();
        $categories = Category::all();
        return view('admin.assets.index_tree', compact('locations', 'categories'));
    }

    /**
     * API: Ambil Daftar Aset (DENGAN PAGINATION)
     */
    public function getByLocation($locationId)
    {
        // Ubah get() menjadi paginate()
        $assets = Asset::where('location_id', $locationId)
                    ->with('category')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10); // Menampilkan 10 data per halaman

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    public function getByCategory($categoryId)
    {
        $assets = Asset::where('category_id', $categoryId)
                    ->with('location') // Load relasi lokasi agar muncul namanya
                    ->orderBy('created_at', 'desc')
                    ->paginate(10); // UBAH get() JADI paginate(10)

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    // ... method getByCategory store ...

    public function store(StoreAssetRequest $request)
    {
        // 1. Ambil data yang sudah divalidasi (aman dari mass-assignment)
        $data = $request->validated();

        // 2. Proses Spesifikasi: buat array asosiatif dari dua array paralel
        $data['specifications'] = $this->formatSpecifications(
            $request->input('specs_key', []),
            $request->input('specs_value', [])
        );

        // 3. Bersihkan key yang bukan kolom DB agar tidak error saat insert
        unset($data['specs_key'], $data['specs_value']);

        // 4. Upload & Optimasi Gambar → otomatis di-resize & dikonversi ke WebP
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadAndOptimizeImage(
                file: $request->file('image'),
                folderPath: 'assets',
                maxWidth: 800,
                quality: 80
            );
        }

        // 5. Simpan (uuid di-generate otomatis oleh boot() di Model)
        $asset = Asset::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Aset berhasil ditambahkan.',
            'data'    => $asset->load(['category', 'location']),
        ], 201);
    }

    /**
     * Show (Detail Aset)
     * FIX BUG: Tambahkan 'location' di with()
     */
    public function show($id)
    {
        // PERBAIKAN DISINI: load relasi 'location' agar namanya muncul di modal detail
        $asset = Asset::with(['category', 'location'])->findOrFail($id);

        $asset->image_url = $asset->image ? asset('storage/' . $asset->image) : null;

        return response()->json([
            'status' => 'success',
            'data' => $asset
        ]);
    }

    // ... method update & destroy biarkan sama seperti sebelumnya ...
    public function update(UpdateAssetRequest $request, $id)
    {
        $asset = Asset::findOrFail($id);

        // 1. Ambil data yang sudah divalidasi
        $data = $request->validated();

        // 2. Proses Spesifikasi
        $data['specifications'] = $this->formatSpecifications(
            $request->input('specs_key', []),
            $request->input('specs_value', [])
        );

        // 3. Bersihkan key yang bukan kolom DB
        unset($data['specs_key'], $data['specs_value']);

        // 4. Upload & Optimasi Gambar baru, hapus file lama otomatis
        if ($request->hasFile('image')) {
            $this->deleteOldImage($asset->image);
            $data['image'] = $this->uploadAndOptimizeImage(
                file: $request->file('image'),
                folderPath: 'assets',
                maxWidth: 800,
                quality: 80
            );
        }

        // 5. Update
        $asset->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data aset berhasil diperbarui.',
            'data'    => $asset->fresh()->load(['category', 'location']),
        ]);
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $this->deleteOldImage($asset->image);
        $asset->delete();
        return response()->json(['status' => 'success', 'message' => 'Deleted']);
    }

    private function formatSpecifications(array $keys, array $values): array
    {
        $specs = [];
        foreach ($keys as $index => $key) {
            // Hanya simpan jika Key tidak kosong & Value ada di index yang sama
            if (!empty($key) && isset($values[$index])) {
                $specs[$key] = $values[$index];
            }
        }
        return $specs;
    }
}
