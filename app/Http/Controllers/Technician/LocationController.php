<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of locations for technician (Read-only)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Location::withCount('assets');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        } else {
            // If no search, sort by tree structure (parent first)
            $query->whereNull('parent_id')->orWhereNotNull('parent_id');
        }

        $locations = $query->orderBy('name')->paginate(15)->appends($request->all());

        return view('technician.locations.index', compact('locations', 'search'));
    }

    /**
     * Display the specified location details and its assets.
     */
    public function show($id)
    {
        $location = Location::with(['assets.category'])->findOrFail($id);
        
        // Count assets by category for statistics
        $categoryStats = $location->assets->groupBy('category.name')->map->count();
        
        // Count assets by status
        $statusStats = [
            'normal' => $location->assets->where('status', 'normal')->count(),
            'rusak' => $location->assets->where('status', 'rusak')->count(),
            'maintenance' => $location->assets->where('status', 'maintenance')->count(),
            'hilang' => $location->assets->where('status', 'hilang')->count(),
        ];

        return view('technician.locations.show', compact('location', 'categoryStats', 'statusStats'));
    }

    /**
     * Process scanned location code 
     * Different from Patrol Scan because this shows all assets regardless of schedule
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrCode = trim($request->qr_code);
        $location = Location::whereRaw('UPPER(code) = ?', [strtoupper($qrCode)])->first();

        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi tidak ditemukan. QR Code: "' . $qrCode . '".'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('technician.locations.show', $location->id)
        ]);
    }

    /**
     * API: Get hierarchical location tree
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
}
