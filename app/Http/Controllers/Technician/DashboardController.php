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
        $patrolQuery = \App\Models\Maintenance::with(['asset.location', 'asset.parentAsset.location', 'location', 'maintenancePlan.shift'])
            ->whereDate('scheduled_date', $now->toDateString())
            ->where('type', 'preventive')
            ->whereIn('status', ['pending', 'in_progress']);

        // Filter by user's shift: show matching shift OR tasks without shift (backward-compatible)
        if ($user->shift_id) {
            $patrolQuery->where(function($q) use ($user) {
                $q->whereHas('maintenancePlan', function($sub) use ($user) {
                    $sub->where('shift_id', $user->shift_id);
                })->orWhereHas('maintenancePlan', function($sub) {
                    $sub->whereNull('shift_id');
                })->orWhereNull('maintenance_plan_id');
            });
        }

        $patrols = $patrolQuery->get();

        // 5. STATS
        $completedToday = WorkOrderHistory::where('user_id', $user->id)
            ->where('action', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $stats = [
            'pending' => $myTasks->count(),
            'completed_today' => $completedToday
        ];

        $patrols = $patrols->groupBy(function ($item) {
            $locId = 0;
            if ($item->location_id) {
                $locId = $item->location_id;
            } elseif ($item->asset) {
                if ($item->asset->location_id) {
                    $locId = $item->asset->location_id;
                } elseif ($item->asset->parentAsset && $item->asset->parentAsset->location_id) {
                    $locId = $item->asset->parentAsset->location_id;
                }
            } elseif ($item->target_asset_ids && is_array($item->target_asset_ids) && count($item->target_asset_ids) > 0) {
                $firstId = $item->target_asset_ids[0];
                $firstAsset = \App\Models\Asset::with('parentAsset')->find($firstId);
                if ($firstAsset) {
                    if ($firstAsset->location_id) $locId = $firstAsset->location_id;
                    if ($firstAsset->parentAsset && $firstAsset->parentAsset->location_id) $locId = $firstAsset->parentAsset->location_id;
                }
            }
            
            $planId = $item->maintenance_plan_id ?: 0;
            
            return $locId . '-' . $planId;
        });

        return view('technician.dashboard', compact('greeting', 'user', 'stats', 'poolTasks', 'myTasks', 'patrols', 'handoverTasks', 'userReports'));
    }
}