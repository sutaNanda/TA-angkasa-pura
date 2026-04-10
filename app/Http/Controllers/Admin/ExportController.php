<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export Laporan Detail Maintenance ke PDF
     */
    public function exportMaintenance($id)
    {
        // 1. Coba cari di PatrolLog (Karena menu Riwayat Pengecekan / Logbook Pengecekan Rutin menggunakan model ini)
        $log = \App\Models\PatrolLog::with(['asset.location', 'asset.category', 'technician', 'checklistTemplate'])->find($id);
        
        if ($log) {
            // Transformasi data PatrolLog agar kompatibel dengan view PDF maintenance_report
            $inspectionData = $log->inspection_data;
            if (is_string($inspectionData)) {
                $inspectionData = json_decode($inspectionData, true);
            }
            
            $answers = isset($inspectionData['answers']) ? $inspectionData['answers'] : $inspectionData;
            $itemIds = is_array($answers) ? array_keys($answers) : [];
            
            // Ambil item checklist agar bisa menampilkan pertanyaan/standar di PDF
            $items = \App\Models\ChecklistItem::whereIn('id', $itemIds)->get()->keyBy('id');
            
            $results = collect();
            foreach ($answers as $itemId => $value) {
                if (isset($items[$itemId])) {
                    $results->push((object)[
                        'item' => $items[$itemId],
                        'value' => $value
                    ]);
                }
            }
            
            // Pasangkan ke relasi virtual agar PDF tidak error saat memanggil $maintenance->results
            $log->setRelation('results', $results);
            $maintenance = $log;
            
            // Properti pembantu agar view PDF tetap sinkron
            $maintenance->ticket_number = 'PAT-' . str_pad($log->id, 6, '0', STR_PAD_LEFT);
            $maintenance->started_at = $log->created_at;
            $maintenance->completed_at = $log->created_at;
            $reportTitle = 'Laporan Pengecekan Rutin & Patroli';
        } else {
            // 2. Jika tidak ada di PatrolLog, cari di model Maintenance asli (Daftar Tugas Maintenance)
            $maintenance = Maintenance::with([
                'asset.location', 
                'asset.category', 
                'technician', 
                'checklistTemplate.items', 
                'results.checklistItem' // Menggunakan relasi results yang baru kita buat
            ])->findOrFail($id);

            // Mapping agar PDF menggunakan properti yang sama
            $maintenance->results->each(function($res) {
                // Agar pdf bisa panggil $result->item->question
                $res->item = $res->checklistItem;
                $res->value = $res->answer;
            });
            $reportTitle = 'Laporan Perawatan Terjadwal (Maintenance)';
        }

        // Siapkan data untuk view PDF
        $data = [
            'maintenance' => $maintenance,
            'title' => $reportTitle,
            'date' => date('d F Y')
        ];

        // Load view dan pass data
        $pdf = Pdf::loadView('admin.pdf.maintenance_report', $data)
                  ->setPaper('a4', 'portrait');

        // Penamaan file yang rapi
        $assetName = $maintenance->asset ? str_replace(' ', '_', $maintenance->asset->name) : 'Aset_Umum';
        $filename = 'Laporan_' . ($log ? 'Patroli' : 'Maintenance') . '_' . $assetName . '_' . date('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Laporan Tiket (Work Orders) ke PDF
     */
    public function exportWorkOrders(Request $request)
    {
        $query = \App\Models\WorkOrder::with([
            'asset' => function ($q) { $q->withTrashed(); },
            'asset.location' => function ($q) { $q->withTrashed(); },
            'technician'
        ])->latest();

        // Optional filter pas export
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->get();

        $data = [
            'tickets' => $tickets,
            'title' => 'Laporan Rekapitulasi Tiket Perbaikan (Work Orders)',
            'date' => date('d F Y'),
            'filter_start' => $request->start_date,
            'filter_end' => $request->end_date,
            'filter_status' => $request->status
        ];

        $pdf = Pdf::loadView('admin.pdf.work_orders_report', $data)
                  ->setPaper('a4', 'landscape'); // Landscape agar tabel lebih muat

        $filename = 'Laporan_Tiket_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Log Aktivitas (Audit Logs) ke PDF
     */
    public function exportAuditLogs(Request $request)
    {
        $query = \App\Models\AuditLog::with('user')->latest();

        // Optional filter pas export
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->get();

        $data = [
            'logs' => $logs,
            'title' => 'Laporan Log Aktivitas Sistem',
            'date' => date('d F Y'),
            'filter_start' => $request->start_date,
            'filter_end' => $request->end_date,
            'filter_module' => $request->module
        ];

        $pdf = Pdf::loadView('admin.pdf.audit_logs_report', $data)
                  ->setPaper('a4', 'landscape');

        $filename = 'Laporan_Audit_Log_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Laporan Lokasi & Aset (Flat Format) ke PDF
     */
    public function exportAssetLocation()
    {
        // 1. Ambil data secara flat, eager load relasi, dan sort seperti permintaan
        $assets = \App\Models\Asset::with(['category', 'location'])
            ->orderBy('location_id')
            ->orderBy('name')
            ->get();

        $data = [
            'assets' => $assets,
            'title' => 'Laporan Rekapitulasi Lokasi & Inventaris Aset',
            'date' => date('d F Y')
        ];

        // 2. Passing ke view PDF menggunakan orientasi Landscape agar tabel pas
        $pdf = Pdf::loadView('admin.pdf.asset_location_report', $data)
                  ->setPaper('A4', 'landscape');

        $filename = 'Laporan_Inventaris_Aset_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportMaintenances(Request $request)
    {
        // Menyalin base query dari MaintenanceController
        $query = \App\Models\PatrolLog::with([
            'asset' => function ($q) {
                $q->withTrashed();
            },
            'location' => function ($q) {
                $q->withTrashed();
            },
            'shift', 
            'technician', 
            'checklistTemplate', 
            'workOrder'
        ])->orderBy('created_at', 'desc');

        // Filter Waktu
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status == 'pass') $status = 'normal';
            if ($status == 'fail') $status = 'issue_found';
            $query->where('status', $status);
        }

        // Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('serial_number', 'like', "%$search%");
            })->orWhereHas('technician', function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        // Ambil SEMUA data tanpa pagination untuk di-export
        $logs = $query->get();

        $data = [
            'logs' => $logs,
            'title' => 'Logbook Laporan Pengecekan Rutin',
            'date' => date('d F Y')
        ];

        $pdf = Pdf::loadView('admin.pdf.maintenances_log_report', $data)
                  ->setPaper('a4', 'landscape'); // Menggunakan format landscape karena kolom banyak

        $filename = 'Laporan_Pengecekan_Rutin_' . date('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }
}
