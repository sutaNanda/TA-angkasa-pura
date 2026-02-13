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

        // Cari Lokasi berdasarkan QR Code (case-insensitive dan trim whitespace)
        $qrCode = trim($request->qr_code);
        $location = Location::whereRaw('UPPER(code) = ?', [strtoupper($qrCode)])->first();

        // Debug: Cek semua lokasi yang ada
        if (!$location) {
            $allLocations = Location::select('id', 'name', 'code')->get();
            \Log::warning('Location not found', [
                'searched_code' => $qrCode,
                'available_locations' => $allLocations->toArray()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi tidak ditemukan. QR Code: "' . $qrCode . '". Pastikan migration sudah dijalankan dan location codes sudah di-seed.',
                'debug' => [
                    'searched' => $qrCode,
                    'available_codes' => $allLocations->pluck('code')->filter()->values()
                ]
            ], 404);
        }

        // Jika ketemu, redirect ke halaman detail lokasi
        return response()->json([
            'status' => 'success',
            'redirect_url' => route('technician.scan.show', $location->id)
        ]);
    }

    // 3. Tampilkan Daftar Aset di Lokasi Tersebut + Today's Maintenance Tasks
    public function show($id)
    {
        $today = now()->toDateString();
        
        $location = Location::with([
            'assets' => function($q) use ($today) {
                $q->with([
                    'category',
                    'maintenances' => function($mq) use ($today) {
                        $mq->where('scheduled_date', $today)
                           ->where('type', 'preventive')
                           ->whereIn('status', ['pending', 'in_progress'])
                           ->with('checklistTemplate');
                    }
                ]);
            }
        ])->findOrFail($id);
        
        // Filter: Only show assets that have maintenance tasks scheduled for today
        $assetsWithTasks = $location->assets->filter(function($asset) {
            return $asset->maintenances->isNotEmpty();
        });
        
        // Calculate statistics
        $stats = [
            'total_tasks' => $assetsWithTasks->sum(fn($a) => $a->maintenances->count()),
            'total_assets' => $assetsWithTasks->count(),
            'daily_tasks' => $assetsWithTasks->sum(fn($a) => 
                $a->maintenances->filter(fn($m) => $m->checklistTemplate->frequency === 'daily')->count()
            ),
            'weekly_tasks' => $assetsWithTasks->sum(fn($a) => 
                $a->maintenances->filter(fn($m) => $m->checklistTemplate->frequency === 'weekly')->count()
            ),
            'monthly_tasks' => $assetsWithTasks->sum(fn($a) => 
                $a->maintenances->filter(fn($m) => $m->checklistTemplate->frequency === 'monthly')->count()
            ),
        ];
        
        return view('technician.scan.show', [
            'location' => $location,
            'assets' => $assetsWithTasks,
            'stats' => $stats,
        ]);
    }
}