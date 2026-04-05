<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PatrolLogReportController extends Controller
{
    /**
     * Menampilkan halaman filter dan tabel laporan Patrol Log
     */
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        // Default sorting untuk tampilan website (terbaru di atas)
        $logs = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.reports.patrol-logs.index', compact('logs'));
    }

    /**
     * Cetak Laporan ke format PDF.
     */
    public function printPdf(Request $request)
    {
        $query = $this->buildQuery($request);
        
        // Aturan ketat dari Blueprint: Pengurutan Ascending (kronologis)
        $logs = $query->orderBy('created_at', 'asc')->get();

        $pdf = Pdf::loadView('admin.pdf.patrol_logs_report', compact('logs', 'request'))
                  ->setPaper('A4', 'landscape');

        return $pdf->stream('Laporan_Logbook_Patroli_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Helper Query Filter Dinamis
     */
    private function buildQuery(Request $request)
    {
        // 4. Eager Loading Relasi
        $query = PatrolLog::with(['technician', 'location', 'asset', 'checklistTemplate']);

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return $query;
    }
}
