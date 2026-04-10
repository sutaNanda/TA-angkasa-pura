<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Location;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Dashboard User: Menampilkan daftar tiket saya.
     */
    public function index()
    {
        // Ambil tiket yang dilaporkan oleh user yang sedang login
        $tickets = WorkOrder::where('reported_by', Auth::id())
                            ->with(['asset', 'asset.location', 'location', 'technician'])
                            ->latest()
                            ->paginate(10);

        return view('user.tickets.index', compact('tickets'));
    }

    /**
     * Form Buat Laporan Baru.
     */
    public function create()
    {
        // Ambil Root Locations (Gedung/Area Utama) untuk dropdown pertama
        // Asumsi: Root location adalah yang parent_id nya NULL
        $rootLocations = Location::whereNull('parent_id')->get();
        
        // Halaman ini akan menggunakan Alpine.js untuk cascading dropdown
        return view('user.tickets.create', compact('rootLocations'));
    }

    /**
     * AJAX: Ambil Sub-Lokasi (Lantai/Ruangan) berdasarkan Parent ID.
     */
    public function getLocations($parentId)
    {
        $locations = Location::where('parent_id', $parentId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    /**
     * AJAX: Ambil Aset berdasarkan Location ID (Recursive).
     * Saat user pilih Gedung, kita cari semua aset di Gedung tsb DAN anak-anaknya.
     */
    public function getAssets($locationId)
    {
        $location = Location::find($locationId);
        
        if (!$location) {
            return response()->json(['status' => 'error', 'message' => 'Location not found'], 404);
        }

        // Ambil aset HANYA yang berada tepat di lokasi tersebut (Exact match location_id)
        // Hilangkan sub-query recursive atau software agnostik agar ketika 
        // User memilih Gedung/Lantai, aset yang muncul spesifik di titik tersebut saja.
        $assets = Asset::where('location_id', $locationId)
                       ->with('category') // Eager load category
                       ->select('id', 'name', 'serial_number', 'status', 'category_id')
                       ->orderBy('name')
                       ->get();

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    /**
     * Simpan Tiket Baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'asset_id' => 'nullable|exists:assets,id',
            'issue_description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:2048',
        ]);

        try {
            // 1. Handle Multi-File Upload
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $photoPaths[] = $photo->store('tickets', 'public');
                }
            }

            // 2. Determine location_id and asset_id
            $locationId = $request->location_id;
            $assetId = $request->asset_id ?: null;

            $ticket = WorkOrder::create([
                'asset_id' => $assetId,
                'location_id' => $locationId,
                'reported_by' => Auth::id(),
                'issue_description' => $request->issue_description,
                'priority' => $request->priority,
                'status' => 'open',
                'source' => 'manual_ticket',
                'initial_photo' => $photoPaths[0] ?? null,
                'photos_before' => !empty($photoPaths) ? $photoPaths : null,
            ]);

            return redirect()->route('user.tickets.index')
                             ->with('success', 'Laporan berhasil dibuat! Kode Tiket: ' . $ticket->ticket_number);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
