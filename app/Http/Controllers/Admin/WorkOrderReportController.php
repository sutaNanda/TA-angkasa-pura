<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WorkOrderReportController extends Controller
{
    /**
     * Menampilkan laman filter dan histori Laporan WO.
     */
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        
        // Paginasi 15 tiket & pertahankan filter di URL params
        $workOrders = $query->paginate(15)->withQueryString();
        
        $this->calculateMTTR($workOrders);

        return view('admin.reports.work-orders.index', compact('workOrders'));
    }

    /**
     * Method untuk Cetak Data PDF.
     */
    public function printPdf(Request $request)
    {
        $query = $this->buildQuery($request);
        $workOrders = $query->get();
        
        $this->calculateMTTR($workOrders);

        // Cetak dengan posisi kertas Landscape agar tabel lebar muat
        $pdf = Pdf::loadView('admin.reports.work-orders.pdf', compact('workOrders', 'request'))
                  ->setPaper('A4', 'landscape');

        // Setel ke stream (preview di browser) sebelum di-download user
        return $pdf->stream('Laporan_Maintenance_AngkasaPura_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * HELPER: Rangkai Query Filtering & Eager Loading (Hindari N+1 Problem)
     */
    private function buildQuery(Request $request)
    {
        // 1. Eager Loading Relasi Tabel (JOIN)
        $query = WorkOrder::with(['asset', 'location', 'technician', 'reporter'])
                          ->orderBy('created_at', 'desc');

        // 2. Filter Rentang Waktu (Date Range)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // 3. Filter Status Penyelesaian
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 4. Filter Kategori Prioritas
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        return $query;
    }

    /**
     * HELPER: Eksekusi Perhitungan Mean Time To Repair (MTTR)
     */
    private function calculateMTTR($workOrders)
    {
        foreach ($workOrders as $wo) {
            // Kita pastikan tiket memiliki jam diciptakan & jam penyelesaian
            if ($wo->completed_at && $wo->created_at) {
                $start = Carbon::parse($wo->created_at);
                $end = Carbon::parse($wo->completed_at);
                
                $diffInMinutes = $start->diffInMinutes($end);
                
                // Konversi Menit menjadi lebih Readable (X Jam Y Menit)
                if ($diffInMinutes < 60) {
                    $wo->mttr_display = $diffInMinutes . ' Menit';
                } else {
                    $hours = floor($diffInMinutes / 60);
                    $mins  = $diffInMinutes % 60;
                    $wo->mttr_display = $hours . ' Jam ' . ($mins > 0 ? $mins . ' Mnt' : '');
                }
            } else {
                // Return '-' jika belum selesai
                $wo->mttr_display = '-'; 
            }
        }
    }
}
