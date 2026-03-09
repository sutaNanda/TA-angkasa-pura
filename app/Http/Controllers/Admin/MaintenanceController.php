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

        // Ambil data dengan Pagination & Eager Loading
        $logs = $query->with(['technician', 'asset.location', 'location', 'workOrder'])
                      ->latest()
                      ->paginate(10);

        // FALLBACK: Untuk data lama yang belum punya work_order_id
        $logs->getCollection()->transform(function($log) {
            if (!$log->workOrder && $log->status == 'issue_found') {
                $wo = \App\Models\WorkOrder::where(function($q) use ($log) {
                        if($log->asset_id) $q->where('asset_id', $log->asset_id);
                        else if($log->location_id) $q->where('location_id', $log->location_id);
                    })
                    ->whereBetween('created_at', [
                        $log->created_at->subMinutes(5), 
                        $log->created_at->addMinutes(5)
                    ])
                    ->first();
                if ($wo) $log->setRelation('workOrder', $wo);
            }
            return $log;
        });

        return view('admin.maintenances.index', compact('logs'));
    }

    // API: Ambil Detail Patrol Log untuk Modal
    public function show($id)
    {
        // 1. Ambil Log Dasar dengan data Tiket & History lengkap
        $log = PatrolLog::with([
            'asset.location', 
            'location', 
            'technician', 
            'workOrder.reporter', 
            'workOrder.technician', 
            'workOrder.histories.user'
        ])->findOrFail($id);

        /**
         * FALLBACK: Jika work_order_id null tapi log ada issue, 
         * coba cari WO yang dibuat di lokasi/aset yang sama di hari yang sama
         */
        if (!$log->workOrder && $log->status == 'issue_found') {
            $fallbackWo = \App\Models\WorkOrder::where(function($q) use ($log) {
                                if($log->asset_id) $q->where('asset_id', $log->asset_id);
                                else if($log->location_id) $q->where('location_id', $log->location_id);
                            })
                            ->whereBetween('created_at', [
                                $log->created_at->subMinutes(5), 
                                $log->created_at->addMinutes(5)
                            ])
                            ->with(['reporter', 'technician', 'histories.user'])
                            ->first();
            
            if ($fallbackWo) {
                $log->setRelation('workOrder', $fallbackWo);
            }
        }
        
        // 2. Parse Data Inspeksi
        $inspectionData = $log->inspection_data;
        if (is_string($inspectionData)) {
            $inspectionData = json_decode($inspectionData, true);
        }

        $answers = isset($inspectionData['answers']) ? $inspectionData['answers'] : $inspectionData;
        $itemIds = is_array($answers) ? array_keys($answers) : [];

        // 3. Ambil Seluruh Item yang Dijawab (Mendukung Lintas Template)
        $items = \App\Models\ChecklistItem::whereIn('id', $itemIds)
                    ->with('template')
                    ->orderBy('checklist_template_id')
                    ->orderBy('order')
                    ->get();

        // 4. Transformasi agar Frontend Bisa Mengenali Struktur "Unified"
        $groupedItems = [];
        $templates = $items->groupBy('checklist_template_id');

        foreach ($templates as $templateId => $templateItems) {
            $template = $templateItems->first()->template;
            $groupedItems[] = [
                'template_name' => $template ? $template->name : 'Lainnya',
                'items' => $templateItems
            ];
        }

        // Tambahkan ke objek log agar JS bisa membaca
        $log->grouped_items = $groupedItems;
        
        return response()->json([
            'status' => 'success',
            'data' => $log
        ]);
    }

    /**
     * Menampilkan Daftar Tugas Maintenance (Pending/Ongoing)
     */
    public function tasks(Request $request)
    {
        $query = \App\Models\Maintenance::with(['asset.location', 'location', 'maintenancePlan', 'technician'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('scheduled_date', 'asc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('scheduled_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('asset', fn($a) => $a->where('name', 'like', "%$search%"))
                  ->orWhereHas('location', fn($l) => $l->where('name', 'like', "%$search%"));
            });
        }

        $tasks = $query->paginate(15);

        return view('admin.maintenances.tasks', compact('tasks'));
    }

    /**
     * Reschedule Tugas Maintenance
     */
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
        ]);

        $maintenance = \App\Models\Maintenance::findOrFail($id);
        
        if ($maintenance->status === 'completed') {
            return back()->with('error', 'Tugas yang sudah selesai tidak bisa di-reschedule.');
        }

        $oldDate = $maintenance->scheduled_date->format('d/m/Y');
        $maintenance->update([
            'scheduled_date' => $request->scheduled_date
        ]);

        return back()->with('success', "Tugas berhasil di-reschedule dari {$oldDate} ke " . $maintenance->scheduled_date->format('d/m/Y'));
    }
}