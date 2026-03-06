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
     * API: Ambil Daftar Aset (DENGAN PAGINATION + SUB-LOKASI)
     */
    public function getByLocation($locationId)
    {
        // Tangani kasus aset tanpa lokasi (Software/Virtual)
        if ($locationId === 'unassigned') {
            $assets = Asset::whereNull('location_id')
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json(['status' => 'success', 'data' => $assets]);
        }

        // 1. Dapatkan model lokasi saat ini
        $location = Location::with('childrenRecursive')->find($locationId);
        
        if (!$location) {
            return response()->json(['status' => 'error', 'message' => 'Location not found'], 404);
        }

        // 2. Ambil ID lokasi ini + semua anak, cucu, cicitnya (rekursif)
        $locationIds = $this->getAllLocationIds($location);
        
        \Log::info("Fetching assets for Location {$locationId}. Effective IDs: " . implode(',', $locationIds));

        // 3. Query Aset berdasarkan kumpulan ID lokasi tersebut
        $assets = Asset::whereIn('location_id', $locationIds)
                    ->with('category')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10); // Menampilkan 10 data per halaman

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    /**
     * Helper recursive untuk mendapatkan array ID Lokasi + cabangnya.
     */
    private function getAllLocationIds($location)
    {
        $ids = [$location->id];
        foreach ($location->childrenRecursive as $child) {
            $ids = array_merge($ids, $this->getAllLocationIds($child));
        }
        return $ids;
    }

    public function getByCategory(Request $request, $categoryId)
    {
        $query = Asset::where('category_id', $categoryId)
                    ->with('location') // Load relasi lokasi agar muncul namanya
                    ->orderBy('created_at', 'desc');

        if ($request->has('all')) {
            $assets = $query->get();
        } else {
            $assets = $query->paginate(10); 
        }

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    public function getByCategories(Request $request)
    {
        $categoryIds = $request->input('category_ids');
        
        if (is_string($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        if (empty($categoryIds)) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $query = Asset::whereIn('category_id', $categoryIds)
                    ->with(['location', 'category'])
                    ->orderBy('name', 'asc');

        if ($request->has('all')) {
            $assets = $query->get();
        } else {
            $assets = $query->paginate(20);
        }

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
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            if (!is_array($files)) {
                $files = [$files];
            }
            $imagePaths = [];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $imagePaths[] = $this->uploadAndOptimizeImage(
                        file: $file,
                        folderPath: 'assets',
                        maxWidth: 800,
                        quality: 80
                    );
                }
            }
            $data['images'] = $imagePaths;
            $data['image'] = null; // Clear old single image column to prevent duplication
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

        // Generate full URLs for multiple images
        $asset->image_urls = [];
        if (is_array($asset->images) && count($asset->images) > 0) {
            $asset->image_urls = array_map(fn($img) => asset('storage/' . $img), $asset->images);
        }

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
        unset($data['specs_key'], $data['specs_value'], $data['kept_images']);

        // 4. Proses gambar: gabungkan kept_images + upload baru
        $keptImages = $request->input('kept_images', []);
        $allOldImages = is_array($asset->images) ? $asset->images : [];

        // Hapus gambar yang TIDAK ada di kept_images
        foreach ($allOldImages as $oldImg) {
            if (!in_array($oldImg, $keptImages)) {
                $this->deleteOldImage($oldImg);
            }
        }

        // Upload gambar baru
        $newImagePaths = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Pastikan selalu array (jika hanya 1 file, bisa non-array)
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $newImagePaths[] = $this->uploadAndOptimizeImage(
                        file: $file,
                        folderPath: 'assets',
                        maxWidth: 800,
                        quality: 80
                    );
                }
            }
        }

        // Merge: existing yang dipertahankan + yang baru diupload
        $data['images'] = array_merge($keptImages, $newImagePaths);
        $data['image'] = null; // Clear old single image column

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
        
        // Hapus banyak gambar jika ada
        if (is_array($asset->images)) {
            foreach ($asset->images as $img) {
                $this->deleteOldImage($img);
            }
        } elseif ($asset->image) {
            $this->deleteOldImage($asset->image);
        }

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
