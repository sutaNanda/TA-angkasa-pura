<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of assets for technician (Read-only)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Asset::with(['category', 'location']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $assets = $query->paginate(15)->appends($request->all());
        $categories = Category::orderBy('name')->get();

        return view('technician.assets.index', compact('assets', 'categories', 'search', 'categoryId'));
    }

    /**
     * API: Ambil Daftar Aset (DENGAN PAGINATION + TOGGLE SUB-LOKASI)
     */
    public function getByLocation(Request $request, $id)
    {
        // Tangani kasus aset tanpa lokasi (Software/Virtual)
        if ($id === 'unassigned') {
            $assets = Asset::whereNull('location_id')
                ->with(['category', 'location'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json(['status' => 'success', 'data' => $assets]);
        }

        $location = \App\Models\Location::with('childrenRecursive')->find($id);
        
        if (!$location) {
            return response()->json(['status' => 'error', 'message' => 'Location not found'], 404);
        }

        // Cek parameter toggle: include_sub (default = true)
        $includeSub = filter_var($request->query('include_sub', true), FILTER_VALIDATE_BOOLEAN);

        if ($includeSub) {
            $locationIds = $this->getAllLocationIds($location);
        } else {
            $locationIds = [$location->id];
        }

        $assets = Asset::whereIn('location_id', $locationIds)
                    ->with(['category', 'location'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

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
        if ($location->relationLoaded('childrenRecursive')) {
            foreach ($location->childrenRecursive as $child) {
                $ids = array_merge($ids, $this->getAllLocationIds($child));
            }
        }
        return $ids;
    }

    /**
     * Display the specified asset details (Smart: JSON for modal, View for Page).
     */
    public function show(Request $request, $id)
    {
        $asset = Asset::with([
            'category', 
            'location', 
            'parentAsset.location', 
            'childAssets.category',
            'maintenances' => function($q) {
                $q->latest('scheduled_date')->take(10);
            }, 
            'workOrders' => function($q) {
                $q->latest('created_at')->take(10);
            }
        ])->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            // Generate full URLs for multiple images
            $asset->image_urls = [];
            if (is_array($asset->images) && count($asset->images) > 0) {
                $asset->image_urls = array_map(fn($img) => asset('storage/' . $img), $asset->images);
            } elseif ($asset->image) {
                $asset->image_urls = [asset('storage/' . $asset->image)];
            }

            return response()->json([
                'status' => 'success',
                'data' => $asset
            ]);
        }

        return view('technician.assets.show', compact('asset'));
    }
}
