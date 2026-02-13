<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatrolLog;

class MaintenanceController extends Controller
{
    /**
     * Menampilkan Riwayat Patroli (Patrol Logs)
     * Menggantikan fungsi lama Maintenance Schedule logs
     */
    public function index(Request $request)
    {
        // Query Dasar ke PatrolLog
        $query = PatrolLog::with(['asset.location', 'technician', 'checklistTemplate', 'workOrder.histories.user'])
                    ->orderBy('created_at', 'desc');

        // 1. Filter Tanggal (Berdasarkan Waktu Patroli)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Tambahkan waktu 00:00:00 dan 23:59:59 agar akurat
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        // 2. Filter Status
        if ($request->filled('status')) {
            // Mapping dari View (pass/fail) ke Database (normal/issue_found)
            $status = $request->status;
            if ($status == 'pass') $status = 'normal';
            if ($status == 'fail') $status = 'issue_found';
            
            $query->where('status', $status);
        }

        // 3. Pencarian (Nama Aset / Nama Teknisi / Serial Number)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('serial_number', 'like', "%$search%");
            })->orWhereHas('technician', function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        // Ambil data dengan Pagination
        $logs = $query->paginate(10);

        return view('admin.maintenances.index', compact('logs'));
    }

    // API: Ambil Detail Patrol Log untuk Modal
    public function show($id)
    {
        // Eager load relasi yang diperlukan (Termasuk Timeline History)
        $log = PatrolLog::with(['asset.location', 'technician', 'checklistTemplate.items', 'workOrder.histories.user'])->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $log
        ]);
    }
}