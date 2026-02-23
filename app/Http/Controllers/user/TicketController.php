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
                            ->with(['asset', 'asset.location'])
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
        // 1. Cari semua ID lokasi turunan (Gedung -> Lantai -> Ruangan)
        // Note: Model Location menggunakan custom 'path', bukan NestedSet trait.
        // Jadi kita manual cari berdasarkan path prefix.
        
        $location = Location::find($locationId);
        
        if (!$location) {
            return response()->json(['status' => 'error', 'message' => 'Location not found'], 404);
        }

        // Ambil semua lokasi yang path-nya diawali oleh path lokasi ini (Descendants) + Diri sendiri
        // Contoh: Path Gedung = "1". Path Lantai = "1/2".
        // Query: where path LIKE "1/%" OR id = 1
        $locationIds = Location::where('path', 'like', $location->path . '/%')
                               ->orWhere('id', $location->id)
                               ->pluck('id');

        // 2. Ambil aset di lokasi-lokasi tersebut
        $assets = Asset::whereIn('location_id', $locationIds)
                       ->select('id', 'name', 'serial_number', 'status')
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
            'asset_id' => 'required|exists:assets,id',
            'issue_description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high',
            'photo' => 'nullable|image|max:2048', // Max 2MB
        ]);

        try {
            // 1. Handle File Upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('tickets', 'public');
            }

            // 2. Create Ticket
            // Note: ticket_number handled by Boot method in Model if empty
            // But we can also set it here if we want specific logic, 
            // relying on Model boot is safer for consistency.

            $ticket = WorkOrder::create([
                // 'ticket_number' => auto-generated,
                'asset_id' => $request->asset_id,
                'reported_by' => Auth::id(),
                'issue_description' => $request->issue_description,
                'priority' => $request->priority,
                'status' => 'open',           // Default status
                'source' => 'manual_ticket',  // Penanda manual
                'initial_photo' => $photoPath, // Tetap simpan di legacy
                'photo_before' => $photoPath,  // Simpan di kolom utama
            ]);

            return redirect()->route('user.tickets.index')
                             ->with('success', 'Laporan berhasil dibuat! Kode Tiket: ' . $ticket->ticket_number);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
