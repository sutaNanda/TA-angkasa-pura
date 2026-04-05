<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class UserReportController extends Controller
{
    public function exportProductivity(Request $request)
    {
        $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;

        // 1. Ambil data users dengan role teknisi (atau 2)
        $users = User::whereIn('role', ['teknisi', '2'])
            ->with('shift') // Load relasi shift
            ->withCount([
                // Hitung Work Orders yang diselesaikan atau diverifikasi
                'assignedWorkOrders as completed_work_orders_count' => function ($query) use ($startDate, $endDate) {
                    $query->whereIn('status', ['completed', 'verified']);
                    if ($startDate && $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                },
                // Hitung total log patroli yang dikerjakan user
                'patrolLogs as total_patrols_count' => function ($query) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                }
            ])
            // Urutkan berdasarkan total tiket diselesaikan terbanyak (DESC)
            ->orderByDesc('completed_work_orders_count')
            ->get();

        // 2. Load PDF dari view table HTML klasik
        $pdf = Pdf::loadView('admin.pdf.technician_productivity_report', compact('users', 'startDate', 'endDate'));
        
        // 3. Atur ukuran kertas
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('laporan-produktivitas-teknisi.pdf');
    }
}
