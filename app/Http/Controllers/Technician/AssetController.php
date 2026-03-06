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
     * Display the specified asset details.
     */
    public function show($id)
    {
        $asset = Asset::with(['category', 'location', 'maintenances' => function($q) {
            $q->latest('scheduled_date')->take(5);
        }, 'workOrders' => function($q) {
            $q->latest('created_at')->take(5);
        }])->findOrFail($id);

        return view('technician.assets.show', compact('asset'));
    }

    /**
     * API: Get assets by location ID for the Tree View Datatable
     */
    public function getByLocation($id)
    {
        $assets = Asset::with('category')->where('location_id', $id)->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }
}
