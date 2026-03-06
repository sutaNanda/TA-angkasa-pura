<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatrolLog;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Menampilkan Riwayat Portofolio Teknisi
     * Berisi: Patroli (Scans) & Perbaikan (Work Orders)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Filter Tanggal (Default: Bulan Ini)
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // ==========================================
        // DATA 1: RIWAYAT PATROLI (Scan QR)
        // ==========================================
        // ==========================================
        $patrols = PatrolLog::with(['asset.location', 'checklistTemplate.items'])
            ->where('technician_id', $user->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        // Grouping per Tanggal
        $groupedPatrols = $patrols->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        // ==========================================
        // DATA 2: RIWAYAT PERBAIKAN (Work Orders)
        // ==========================================
        // Ambil WO yang Selesai oleh User INI
        // ATAU yang pernah di-Handover oleh User INI
        $workOrders = WorkOrder::with(['asset.location', 'histories', 'location'])
            ->where(function($q) use ($user) {
                // Completed by me
                $q->where('technician_id', $user->id)
                  ->where('status', 'completed');
            })
            ->orWhereHas('histories', function($q) use ($user) {
                // Pernah melakukan handover
                $q->where('user_id', $user->id)
                  ->where('action', 'handover');
            })
            ->whereMonth('updated_at', $month) // Gunakan updated_at untuk tanggal penyelesaian/handover
            ->whereYear('updated_at', $year)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Grouping per Tanggal
        $groupedWorkOrders = $workOrders->groupBy(function ($item) {
            return $item->updated_at->format('Y-m-d');
        });

        // ==========================================
        // STATISTIK RINGKASAN
        // ==========================================
        $stats = [
            'total_scan' => $patrols->count(),
            'total_fix' => $workOrders->where('status', 'completed')->count(), // Hanya hitung yang completed
        ];

        return view('technician.history.index', compact('groupedPatrols', 'groupedWorkOrders', 'stats', 'month', 'year'));
    }
}
