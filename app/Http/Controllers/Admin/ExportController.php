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
        // Ambil data maintenance berdasarkan ID
        $maintenance = Maintenance::with([
            'asset.location', 
            'asset.category', 
            'technician', 
            'checklistTemplate.items', 
            'results'
        ])->findOrFail($id);

        // Siapkan data untuk view PDF
        $data = [
            'maintenance' => $maintenance,
            'title' => 'Laporan Perawatan Aset',
            'date' => date('d F Y')
        ];

        // Load view dan pass data
        // setPaper a4 portrait
        $pdf = Pdf::loadView('admin.pdf.maintenance_report', $data)
                  ->setPaper('a4', 'portrait');

        // Render PDF ke browser untuk diunduh
        $filename = 'Laporan_Perawatan_' . str_replace(' ', '_', $maintenance->asset->name) . '_' . date('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Laporan Tiket (Work Orders) ke PDF
     */
    public function exportWorkOrders(Request $request)
    {
        $query = \App\Models\WorkOrder::with(['asset.location', 'technician'])->latest();

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
     * Export Laporan Hirarki Lokasi & Aset (Gabungan) ke PDF
     */
    public function exportAssetLocation()
    {
        // Ambil data lokasi (hanya parent/root) beserta children dan aset-aset di dalamnya
        $locations = \App\Models\Location::with([
            'assets.category', 
            'children.assets.category',
            'children.children.assets.category' // Maksimal 3 level kedalaman untuk laporan
        ])->whereNull('parent_id')->get();

        // Ambil juga aset yang tidak punya lokasi (Unassigned)
        $unassignedAssets = \App\Models\Asset::with('category')->whereNull('location_id')->get();

        $data = [
            'locations' => $locations,
            'unassignedAssets' => $unassignedAssets,
            'title' => 'Laporan Rekapitulasi Lokasi & Inventaris Aset',
            'date' => date('d F Y')
        ];

        $pdf = Pdf::loadView('admin.pdf.asset_location_report', $data)
                  ->setPaper('a4', 'portrait');

        $filename = 'Laporan_Lokasi_dan_Aset_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportMaintenances(Request $request)
    {
        // Menyalin base query dari MaintenanceController
        $query = \App\Models\PatrolLog::with(['asset.location', 'technician', 'checklistTemplate', 'workOrder'])
                    ->orderBy('created_at', 'desc');

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
