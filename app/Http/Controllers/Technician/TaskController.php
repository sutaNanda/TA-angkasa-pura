<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Models\User;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'my_tasks'); // Default tab changed

        // 1. TUGAS SAYA (My Tasks)
        // Milik user login, status belum completed
        $myTasks = WorkOrder::with(['asset.location', 'location'])
            ->where('technician_id', $user->id)
            ->whereIn('status', ['assigned', 'open', 'in_progress', 'pending_part'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')") // Prioritas
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. TUGAS POOL (Available)
        // Belum ada teknisi (Open) ATAU Handover dari teknisi lain
        // 2. TUGAS POOL (Available)
        // Belum ada teknisi (Open) ATAU Handover dari teknisi lain
        $poolTasks = WorkOrder::with(['asset.location', 'reporter', 'location'])
            ->where(function($q) use ($user) {
                $q->whereNull('technician_id')
                  ->where('status', '!=', 'completed')
                  // Exclude manual_ticket, we want them separate? 
                  // Or include them but sort differently?
                  // Let's keep them here but distinguish in View.
                  // User said "jangan buat jadi handover".
                  // So we just ensure they show as 'Open' (Laporan Baru), not 'Handover'.
            ;})
            ->orWhere(function($q) use ($user) {
                $q->where('status', 'handover')
                  ->where('technician_id', '!=', $user->id); // Handover dari orang lain
            })
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('technician.tasks.index', compact('myTasks', 'poolTasks', 'tab'));
    }

    public function show($id)
    {
        $task = WorkOrder::with(['asset.location', 'reporter', 'histories.user', 'location'])->findOrFail($id);

        $otherTechnicians = User::where('role', 'teknisi')
                                ->where('id', '!=', auth()->id())
                                ->get();

        $locationAssets = [];
        if (!$task->asset_id && $task->location_id) {
            // Tampilkan aset fisik di lokasi tersebut + semua aset virtual/software
            $locationAssets = \App\Models\Asset::where('location_id', $task->location_id)
                                               ->orWhereNull('location_id')
                                               ->get();
        }

        return view('technician.tasks.show', compact('task', 'otherTechnicians', 'locationAssets'));
    }

    // 1. CLAIM TASK (Ambil Tugas)
    public function claim($id)
    {
        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        // Validasi: Hanya bisa claim jika handover atau open
        if (!in_array($task->status, ['open', 'handover'])) {
             return back()->with('error', 'Tugas ini tidak bisa diambil (Status tidak valid).');
        }

        // Logika: Ambil tugas dari Pool

        DB::transaction(function() use ($task, $user) {
            // Update Work Order
            $task->technician_id = $user->id;
            $task->status = 'in_progress';
            $task->save();

            // Create History
            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'picked_up',
                'description' => 'Mengambil tugas dari Pool (Claim).',
            ]);
        });

        return redirect()->route('technician.tasks.index', ['tab' => 'my_tasks'])
                         ->with('success', 'Tugas berhasil diambil & status menjadi In Progress.');
    }

    // 1.5 START TASK (Mulai Kerjakan - untuk Assigned)
    public function start($id)
    {
        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        if ($task->technician_id != $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk memulai tugas ini.');
        }

        // Logika: Mulai mengerjakan

        DB::transaction(function() use ($task, $user) {
            $task->status = 'in_progress';
            $task->save();

            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'in_progress',
                'description' => 'Memulai pekerjaan (Start).',
            ]);
        });

        return back()->with('success', 'Status pekerjaan berubah menjadi In Progress.');
    }

    // 2. COMPLETE TASK (Selesai)
    public function complete(Request $request, $id)
    {
        $task = WorkOrder::findOrFail($id);

        $rules = [
            'description' => 'required|string|min:10',
            'photos' => 'required|array|max:5', // Wajib Foto
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ];

        // Jika tiket belum memiliki asset_id, wajib diidentifikasi saat selesai
        if (!$task->asset_id) {
            $rules['asset_id'] = 'required|exists:assets,id';
        }

        $request->validate($rules);

        $user = auth()->user();

        // Validasi kepemilikan
        if ($task->technician_id != $user->id) {
            return back()->with('error', 'Anda bukan teknisi yang mengerjakan tugas ini.');
        }

        DB::transaction(function() use ($task, $user, $request) {
            // Upload Foto Array
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('completion-photos', 'public');
                }
            }

            // Update Work Order
            if (!$task->asset_id && $request->filled('asset_id')) {
                $task->asset_id = $request->asset_id;
            }
            $task->status = 'completed';
            $task->photos_after = $photoPaths; // Simpan array di kolom utama
            $task->save();

            // Create History
            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'completed',
                'description' => $request->description,
                'photos' => $photoPaths,
            ]);
        });

        return redirect()->route('technician.tasks.index', ['tab' => 'completed'])
                         ->with('success', 'Pekerjaan selesai & dilaporkan.');
    }

    // 3. HANDOVER TASK (Lempar Tugas)
    public function handover(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|min:10', // Alasan handover
            'photos' => 'nullable|array|max:5', // Foto opsional, max 5MB
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'asset_id' => 'nullable|exists:assets,id',
        ]);

        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        // Validasi kepemilikan (Hanya yang sedang mengerjakan bisa handover)
        if ($task->technician_id != $user->id && $task->technician_id != null) {
             return back()->with('error', 'Anda tidak memiliki akses untuk handover tugas ini.');
        }

        DB::transaction(function() use ($task, $user, $request) {
            // Check if photo is uploaded
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('handover-photos', 'public');
                }
            }

            // Update Work Order
            if (!$task->asset_id && $request->filled('asset_id')) {
                $task->asset_id = $request->asset_id;
            }
            $task->technician_id = null; // Lepas ke pool
            $task->status = 'handover';
            $task->save();

            // Create History
            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'handover', 
                'description' => 'Handover: ' . $request->note,
                'photos' => $photoPaths, // Simpan path foto array jika ada
            ]);
        });

        return redirect()->route('technician.tasks.index')
                         ->with('success', 'Tugas berhasil dilepas (Handover) ke pool antrian.');
    }
}
