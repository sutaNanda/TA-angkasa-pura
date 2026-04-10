<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Asset;

class ScanController extends Controller
{
    // 1. Tampilkan Halaman Kamera Scanner
    public function index()
    {
        return view('technician.scan.index');
    }

    // 2. Proses Hasil Scan (Menerima QR Code)
    public function process(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        // Log untuk debugging
        \Log::info('QR Code Scanned', [
            'qr_code' => $request->qr_code,
            'trimmed' => trim($request->qr_code)
        ]);

        // Cari Lokasi berdasarkan QR Code, Kode Lokasi, atau Nama Lokasi
        $qrCode = trim($request->qr_code);
        
        // 1. Coba cari persis berdasarkan Code atau Name (LOKASI)
        $location = Location::where('code', $qrCode)
                            ->orWhere('name', $qrCode)
                            ->first();

        // 2. Jika tidak ketemu di lokasi, coba cari di ASET (UUID)
        if (!$location) {
            $asset = Asset::where('uuid', $qrCode)->first();
            if ($asset) {
                return response()->json([
                    'status' => 'success',
                    'redirect_url' => route('technician.inspection.show', $asset->id)
                ]);
            }
        }

        // 3. Jika tidak ketemu secara eksak, coba cari berdasarkan sebagian nama (LIKE)
        if (!$location) {
            $location = Location::where('name', 'LIKE', '%' . $qrCode . '%')->first();
        }

        // Debug: Cek semua lokasi yang ada
        if (!$location) {
            $allLocations = Location::select('id', 'name', 'code')->get();
            \Log::warning('Location not found', [
                'searched_code' => $qrCode,
                'available_locations' => $allLocations->toArray()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Lokal atau Aset tidak ditemukan. Pastikan ID Ruangan / UUID Aset: "' . $qrCode . '" sudah benar.',
            ], 404);
        }

        // Jika ketemu lokasi, redirect ke halaman detail lokasi
        return response()->json([
            'status' => 'success',
            'redirect_url' => route('technician.scan.show', $location->id)
        ]);
    }

    // 3. Tampilkan Daftar Aset di Lokasi Tersebut + Today's Maintenance Tasks
    public function show($id)
    {
        $today = now()->toDateString();
        
        if ($id == 0) {
            $location = new Location();
            $location->id = 0;
            $location->name = 'Virtual / Software';
            $location->description = 'Pemeliharaan aset virtual dan lisensi perangkat lunak.';
            
            $assets = Asset::whereNull('location_id')->with(['category'])->get();
            $maintenanceTasks = \App\Models\Maintenance::whereNull('location_id')
                ->whereDate('scheduled_date', $today)
                ->where('type', 'preventive')
                ->whereIn('status', ['pending', 'in_progress'])
                ->with(['checklistTemplate', 'maintenancePlan', 'asset.category'])
                ->get();
        } else {
            $location = Location::findOrFail($id);
            $assets = Asset::where('location_id', $id)->with(['category'])->get();
            
            // Get maintenance for this location (Grouped OR Single Asset)
            $maintenanceTasks = \App\Models\Maintenance::where('location_id', $id)
                ->whereDate('scheduled_date', $today)
                ->where('type', 'preventive')
                ->whereIn('status', ['pending', 'in_progress'])
                ->with(['checklistTemplate', 'maintenancePlan', 'asset.category'])
                ->get();
        }
        
        // Calculate statistics based on maintenance tasks
        $stats = [
            'total_tasks' => $maintenanceTasks->count(),
            'total_assets' => $assets->count(),
            'daily_tasks' => $maintenanceTasks->filter(function($m) {
                $freq = $m->checklistTemplate->frequency ?? $m->maintenancePlan->frequency ?? 'daily';
                return $freq === 'daily';
            })->count(),
            'weekly_tasks' => $maintenanceTasks->filter(function($m) {
                $freq = $m->checklistTemplate->frequency ?? $m->maintenancePlan->frequency ?? 'weekly';
                return $freq === 'weekly';
            })->count(),
            'monthly_tasks' => $maintenanceTasks->filter(function($m) {
                $freq = $m->checklistTemplate->frequency ?? $m->maintenancePlan->frequency ?? 'monthly';
                return $freq === 'monthly';
            })->count(),
        ];
        
        return view('technician.scan.show', [
            'location' => $location,
            'assets' => $assets,
            'maintenanceTasks' => $maintenanceTasks,
            'stats' => $stats,
        ]);
    }
}