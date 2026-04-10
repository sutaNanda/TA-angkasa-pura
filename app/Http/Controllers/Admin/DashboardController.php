<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. KARTU STATISTIK ATAS (Hari Ini)
        $stats = [
            'patrol_total' => Maintenance::whereDate('schedule_date', $today)->count(),
            'patrol_done'  => Maintenance::whereDate('schedule_date', $today)
                                         ->where('status', 'completed')->count(),

            'lk_open'      => WorkOrder::whereIn('status', ['open', 'in_progress'])->count(),
            'lk_handover'  => WorkOrder::where('status', 'handover')->count(),

            'tech_active'  => User::where('role', 'teknisi')->count(), // Bisa dikembangkan cek login session
        ];

        // Hitung Persentase Patroli
        $stats['patrol_percent'] = $stats['patrol_total'] > 0
            ? round(($stats['patrol_done'] / $stats['patrol_total']) * 100)
            : 0;

        // 2. CHART 1: TREN TIKET 7 HARI TERAKHIR (Line Chart)
        $chartDates = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartDates[] = $date->format('d M');
            $chartValues[] = WorkOrder::whereDate('created_at', $date)->count();
        }

        // 3. CHART 2: KOMPOSISI STATUS TIKET (Doughnut Chart)
        $ticketStatus = [
            'open' => WorkOrder::whereIn('status', ['open', 'in_progress'])->count(),
            'pending' => WorkOrder::whereIn('status', ['pending_part', 'handover'])->count(),
            'completed' => WorkOrder::whereIn('status', ['completed', 'verified'])->count(),
        ];

        // 4. TOP 5 ASET BERMASALAH (Bulan Ini)
        // Mengambil aset dengan jumlah tiket terbanyak bulan ini. Hanya hitung tiket yang sudah selesai (aset teridentifikasi).
        $problematicAssets = WorkOrder::select('asset_id', DB::raw('count(*) as total'))
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereNotNull('asset_id')
            ->whereIn('status', ['completed', 'verified'])
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->with(['asset.location']) // Load relasi aset & lokasi
            ->take(5)
            ->get();

        // 5. AKTIVITAS TERBARU (Gabungan WorkOrder & Maintenance)
        $recentActivities = WorkOrder::with('technician')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'chartDates',
            'chartValues',
            'ticketStatus',
            'problematicAssets',
            'recentActivities'
        ));
    }
}
