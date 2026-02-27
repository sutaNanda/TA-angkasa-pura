<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Asset;

class WorkOrderController extends Controller
{
    /**
     * Menampilkan Halaman List Tiket
     */
    public function index(Request $request)
    {
        // Query Dasar
        $query = WorkOrder::with(['asset.location', 'technician'])->latest();

        // Filter Status Tab
        if ($request->tab == 'open') {
            $query->whereIn('status', ['open', 'handover']); // Handover dianggap butuh perhatian seperti Open
        } elseif ($request->tab == 'verify') {
            $query->where('status', 'completed');
        } elseif ($request->tab == 'progress') {
            $query->whereIn('status', ['in_progress', 'pending_part']);
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search){
                $q->where('ticket_number', 'like', "%$search%")
                  ->orWhereHas('asset', fn($a) => $a->where('name', 'like', "%$search%"));
            });
        }

        $tickets = $query->paginate(10);

        // Data Pendukung untuk Modal & Badge
        $technicians = User::where('role', 'teknisi')->get();
        $assets = Asset::all();
        
        // Hitung Jumlah untuk Badge di Tab
        $counts = [
            'all' => WorkOrder::count(),
            'open' => WorkOrder::whereIn('status', ['open', 'handover'])->count(),
            'verify' => WorkOrder::where('status', 'completed')->count(),
        ];

        return view('admin.work_orders.index', compact('tickets', 'technicians', 'assets', 'counts'));
    }

    /**
     * Simpan Tiket Manual (Create)
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high'
        ]);

        WorkOrder::create([
            'asset_id' => $request->asset_id,
            'issue_description' => $request->issue_description,
            'priority' => $request->priority,
            'status' => 'open',
            'reported_by' => auth()->id(), // ID Admin yang login
            'source' => 'manual_ticket', // Laporan Manual
        ]);

        return back()->with('success', 'Tiket perbaikan berhasil dibuat.');
    }

    /**
     * Tugaskan Teknisi (Assign)
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
            'priority' => 'required'
        ]);

        $ticket = WorkOrder::findOrFail($id);
        
        $ticket->update([
            'technician_id' => $request->technician_id,
            'priority' => $request->priority,
            'status' => 'in_progress' // Status otomatis berubah jadi dikerjakan
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    /**
     * Verifikasi Tiket (Approve/Close)
     */
    public function verify($id)
    {
        $ticket = WorkOrder::findOrFail($id);
        
        if($ticket->status != 'completed') {
            return back()->with('error', 'Tiket belum diselesaikan oleh teknisi.');
        }

        $ticket->update(['status' => 'verified']);

        return back()->with('success', 'Tiket berhasil diverifikasi dan ditutup.');
    }

    /**
     * Bulk Verify All Completed Tickets
     */
    public function verifyAll()
    {
        $count = WorkOrder::where('status', 'completed')->update(['status' => 'verified']);

        if ($count > 0) {
            return back()->with('success', "$count tiket berhasil diverifikasi sekaligus.");
        }

        return back()->with('info', 'Tidak ada tiket yang perlu diverifikasi.');
    }

    /**
     * API: Ambil Detail Tiket (Untuk Modal)
     */
    public function show($id)
    {
        $ticket = WorkOrder::with(['asset.location', 'technician', 'reporter', 'histories'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $ticket
        ]);
    }
}