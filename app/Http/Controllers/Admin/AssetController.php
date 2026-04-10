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
     * API: Ambil Daftar Aset (DENGAN PAGINATION + TOGGLE SUB-LOKASI)
     */
    public function getByLocation(Request $request, $locationId)
    {
        // Tangani kasus aset tanpa lokasi (Software/Virtual)
        if ($locationId === 'unassigned') {
            $assets = Asset::whereNull('location_id')
                ->with(['category', 'location'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json(['status' => 'success', 'data' => $assets]);
        }

        // 1. Dapatkan model lokasi saat ini
        $location = Location::with('childrenRecursive')->find($locationId);
        
        if (!$location) {
            return response()->json(['status' => 'error', 'message' => 'Location not found'], 404);
        }

        // 2. Cek parameter toggle: include_sub (default = true untuk backward compatibility)
        $includeSub = filter_var($request->query('include_sub', true), FILTER_VALIDATE_BOOLEAN);

        if ($includeSub) {
            // Ambil ID lokasi ini + semua anak, cucu, cicitnya (rekursif)
            $locationIds = $this->getAllLocationIds($location);
        } else {
            // Hanya ambil aset di lokasi ini saja (tanpa sub-lokasi)
            $locationIds = [$location->id];
        }
        
        \Log::info("Fetching assets for Location {$locationId}. include_sub={$includeSub}. Effective IDs: " . implode(',', $locationIds));

        // 3. Query Aset berdasarkan kumpulan ID lokasi tersebut
        $assets = Asset::whereIn('location_id', $locationIds)
                    ->with(['category', 'location'])
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
        $data = $request->validated();

        // Logika Parent-Child (Software)
        if (!empty($data['parent_asset_id'])) {
            $data['location_id'] = null;
        }

        $data['specifications'] = $this->formatSpecifications(
            $request->input('specs_key', []),
            $request->input('specs_value', [])
        );
        unset($data['specs_key'], $data['specs_value']);

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
            $data['image'] = null;
        }

        // --- BULK ASSIGNMENT LOGIC ---
        // Jika parent_asset_id adalah array (user memilih lebih dari 1 PC/Hardware induk)
        if (isset($data['parent_asset_id']) && is_array($data['parent_asset_id'])) {
            $createdAssets = [];
            $parentIds = $data['parent_asset_id'];
            
            // Loop sejumlah hardware yang dipilih
            foreach ($parentIds as $parentId) {
                $assetData = $data; // Copy data dasar
                $assetData['parent_asset_id'] = $parentId; // Assign parent_asset_id spesifik
                
                // Unique Validation Bypass (optional, but needed if SN must be unique per row):
                // Jika ingin UUID/SN spesifik per instalasi, handle di sini.
                // Untuk Bulk, biasanya SN diizinkan kembar (karena itu Volume License), 
                // atau ditambahkan suffix (opsional). Di sini kita simpan apa adanya (identik).
                
                $createdAssets[] = Asset::create($assetData)->load(['category', 'location', 'parentAsset']);
            }
            
            return response()->json([
                'status'  => 'success',
                'message' => count($parentIds) . ' Aset perangkat lunak (Bulk Assignment) berhasil ditambahkan.',
                'data'    => $createdAssets[0], // Return salah satu untuk compatibility frontend sementara
            ], 201);
            
        } else {
            // Pembuatan aset tunggal (normal)
            $asset = Asset::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Aset berhasil ditambahkan.',
                'data'    => $asset->load(['category', 'location', 'parentAsset']),
            ], 201);
        }
    }

    /**
     * Show (Detail Aset) — eager-load parent, children, category, location
     */
    public function show($id)
    {
        $asset = Asset::with(['category', 'location', 'parentAsset.location', 'childAssets.category'])->findOrFail($id);

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

    public function update(UpdateAssetRequest $request, $id)
    {
        $asset = Asset::findOrFail($id);
        $data = $request->validated();

        // Handle parent_asset_id jika dikirim sebagai array (karena reuse komponen UI multi-select)
        if (isset($data['parent_asset_id']) && is_array($data['parent_asset_id'])) {
            $data['parent_asset_id'] = !empty($data['parent_asset_id']) ? $data['parent_asset_id'][0] : null;
        }

        // Logika Parent-Child: Software mengikuti lokasi induknya
        if (!empty($data['parent_asset_id'])) {
            $data['location_id'] = null;
        }

        $data['specifications'] = $this->formatSpecifications(
            $request->input('specs_key', []),
            $request->input('specs_value', [])
        );
        unset($data['specs_key'], $data['specs_value'], $data['kept_images']);

        // Proses gambar
        $keptImages = $request->input('kept_images', []);
        $allOldImages = is_array($asset->images) ? $asset->images : [];
        foreach ($allOldImages as $oldImg) {
            if (!in_array($oldImg, $keptImages)) {
                $this->deleteOldImage($oldImg);
            }
        }

        $newImagePaths = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
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

        $data['images'] = array_merge($keptImages, $newImagePaths);
        $data['image'] = null;

        $asset->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data aset berhasil diperbarui.',
            'data'    => $asset->fresh()->load(['category', 'location', 'parentAsset']),
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

    /**
     * API: Ambil daftar aset Hardware (non-software) di lokasi tertentu.
     * Digunakan oleh dropdown "Induk Aset" pada form Software/Lisensi.
     */
    public function getHardwareByLocation($locationId)
    {
        $location = Location::with('childrenRecursive')->find($locationId);
        if (!$location) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $locationIds = $this->getAllLocationIds($location);

        // Ambil hanya aset yang TIDAK memiliki parent (= hardware fisik), bukan software
        $assets = Asset::whereIn('location_id', $locationIds)
                    ->whereNull('parent_asset_id')
                    ->with('category')
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name', 'serial_number', 'category_id']);

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    private function formatSpecifications(array $keys, array $values): array
    {
        $specs = [];
        foreach ($keys as $index => $key) {
            if (!empty($key) && isset($values[$index])) {
                $specs[$key] = $values[$index];
            }
        }
        return $specs;
    }
}
