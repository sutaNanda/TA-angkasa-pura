<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Models\PatrolLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // 1. GREETING
        $hour = $now->hour;
        if ($hour >= 5 && $hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        // 2. MY TASKS (Tiket Perbaikan)
        // Status: in_progress atau assigned (jika ada). Priority descending.
        $myTasks = WorkOrder::with(['asset.location'])
            ->where('technician_id', $user->id)
            ->whereIn('status', ['in_progress', 'assigned', 'open'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. POOL TASKS / HANDOVER
        // Tiket status handover/open yang belum ada teknisi ATAU handover khusus ke saya
        // 3. POOL TASKS / HANDOVER
        // Kita pecah query agar bisa membedakan jenis notifikasi di dashboard
        $basePoolQuery = WorkOrder::with(['asset.location', 'reporter'])
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc');

        // A. Handover (Prioritas Tinggi)
        // Status handover (dari siapapun) ATAU status handover yg spesifik ke user ini (logika lama)
        $handoverTasks = (clone $basePoolQuery)->where(function($q) use ($user) {
            $q->where('status', 'handover')->whereNull('technician_id') // Handover ke publik
              ->orWhere(function($sub) use ($user) {
                  $sub->where('status', 'handover')->where('technician_id', $user->id); // Handover personal
              });
        })->get();

        // B. Laporan User (Manual Ticket) - Open & Belum ada teknisi
        $userReports = (clone $basePoolQuery)->where('status', 'open')
            ->whereNull('technician_id')
            ->where('source', 'manual_ticket')
            ->get();

        // C. Laporan Patroli/System (Open & Belum ada teknisi)
        $poolTasksRaw = (clone $basePoolQuery)->where('status', 'open')
            ->whereNull('technician_id')
            ->where('source', '!=', 'manual_ticket')
            ->get();

        // Gabungkan untuk list di bawah, tapi kita punya variabel terpisah untuk menghitung badge notifikasi
        $poolTasks = $handoverTasks->merge($userReports)->merge($poolTasksRaw);

        // 4. JADWAL PATROLI HARI INI (Using Maintenance Model as requested)
        // Ambil data maintenance (preventive) yang dijadwalkan hari ini dan belum selesai
        $patrols = \App\Models\Maintenance::with(['asset.location', 'location', 'maintenancePlan'])
            ->whereDate('scheduled_date', $now->toDateString())
            ->where('type', 'preventive')
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        // 5. STATS
        $completedToday = WorkOrderHistory::where('user_id', $user->id)
            ->where('action', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $stats = [
            'pending' => $myTasks->count(),
            'completed_today' => $completedToday
        ];

        // Group By Location ID agar tampilan dashboard lebih rapi (Location Cards)
        $patrols = $patrols->groupBy(function ($item) {
            return $item->location_id ?? ($item->asset->location_id ?? 0);
        });

        return view('technician.dashboard', compact('greeting', 'user', 'stats', 'poolTasks', 'myTasks', 'patrols', 'handoverTasks', 'userReports'));
    }
}