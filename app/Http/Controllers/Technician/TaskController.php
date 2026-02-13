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
        $myTasks = WorkOrder::with(['asset.location'])
            ->where('technician_id', $user->id)
            ->whereIn('status', ['assigned', 'open', 'in_progress', 'pending_part'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')") // Prioritas
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. TUGAS POOL (Available)
        // Belum ada teknisi (Open) ATAU Handover dari teknisi lain
        $poolTasks = WorkOrder::with(['asset.location'])
            ->where(function($q) use ($user) {
                $q->whereNull('technician_id')
                  ->where('status', '!=', 'completed');
            })
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
        $task = WorkOrder::with(['asset.location', 'reporter'])->findOrFail($id);

        $otherTechnicians = User::where('role', 'teknisi')
                                ->where('id', '!=', auth()->id())
                                ->get();

        return view('technician.tasks.show', compact('task', 'otherTechnicians'));
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

        // CONSTRAINT: Single Active Task
        // Cek apakah user sedang mengerjakan tugas lain (in_progress)
        $hasActiveTask = WorkOrder::where('technician_id', $user->id)
                                  ->where('status', 'in_progress')
                                  ->exists();

        if ($hasActiveTask) {
            return back()->with('error', 'Selesaikan atau Tunda tugas yang sedang berjalan sebelum mengambil tugas baru!');
        }

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

        // Cek Single Active Task
        $hasActiveTask = WorkOrder::where('technician_id', $user->id)
                                  ->where('status', 'in_progress')
                                  ->exists();

        if ($hasActiveTask) {
            return back()->with('error', 'Selesaikan tugas lain sebelum memulai tugas ini!');
        }

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
        $request->validate([
            'description' => 'required|string|min:10',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Wajib Foto, Max 5MB
        ]);

        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        // Validasi kepemilikan
        if ($task->technician_id != $user->id) {
            return back()->with('error', 'Anda bukan teknisi yang mengerjakan tugas ini.');
        }

        DB::transaction(function() use ($task, $user, $request) {
            // Upload Foto
            $photoPath = $request->file('photo')->store('completion-photos', 'public');

            // Update Work Order
            $task->status = 'completed';
            // Simpan foto terakhir di kolom legacy (opsional, jika masih dipakai dashboard lama)
            $task->last_progress_photo = $photoPath; 
            $task->save();

            // Create History
            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'completed',
                'description' => $request->description,
                'photo' => $photoPath,
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
        ]);

        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        // Validasi kepemilikan (Hanya yang sedang mengerjakan bisa handover)
        if ($task->technician_id != $user->id && $task->technician_id != null) {
             // Exception: jika belum diklaim, anyone can handover? NO. Only assigned user.
             // Tapi jika status sudah handover, tidak perlu handover lagi.
             return back()->with('error', 'Anda tidak memiliki akses untuk handover tugas ini.');
        }

        DB::transaction(function() use ($task, $user, $request) {
            // Update Work Order
            $task->technician_id = null; // Lepas ke pool
            $task->status = 'handover';
            $task->save();

            // Create History
            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id' => $user->id,
                'action' => 'handover', 
                'description' => 'Handover: ' . $request->note,
            ]);
        });

        return redirect()->route('technician.tasks.index')
                         ->with('success', 'Tugas berhasil dilepas (Handover) ke pool antrian.');
    }
}
