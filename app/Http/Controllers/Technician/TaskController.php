<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Models\WorkOrderHandover;
use App\Models\TechnicianGroup;
use App\Models\User;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $tab   = $request->query('tab', 'my_tasks');
        $group = $user->group; // Grup aktif teknisi

        // 1. TUGAS SAYA — yang sedang dieksekusi secara individu
        $myTasks = WorkOrder::with(['asset.location', 'location', 'assignedGroup'])
            ->where('executed_by_user_id', $user->id)
            ->whereIn('status', ['assigned', 'open', 'in_progress', 'pending_part'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. ANTREAN GRUP SAYA — tiket yang di-assign ke grup teknisi ini
        // Termasuk: di-assign spesifik ke grup ATAU dari pool umum
        $groupQueue = collect();
        if ($group) {
            $groupQueue = WorkOrder::with(['asset.location', 'reporter', 'location'])
                ->where(function ($q) use ($group) {
                    $q
                        // Tiket spesifik untuk grup saya
                        ->where('assigned_group_id', $group->id)
                        // ATAU Pool Umum (assigned_group_id null) yang masih open
                        ->orWhereNull('assigned_group_id');
                })
                ->whereNotIn('status', ['completed', 'verified'])
                // Jangan tampilkan yang sudah diambil oleh diri sendiri
                ->where(function ($q) use ($user) {
                    $q->whereNull('executed_by_user_id')
                      ->orWhere('executed_by_user_id', '!=', $user->id);
                })
                ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('technician.tasks.index', compact('myTasks', 'groupQueue', 'tab', 'group'));
    }

    public function show($id)
    {
        $task = WorkOrder::with(['asset.location', 'reporter', 'histories.user', 'location', 'handovers.fromGroup', 'handovers.toGroup', 'handovers.handedOverBy'])->findOrFail($id);

        $groups = TechnicianGroup::orderBy('name')->get();

        $locationAssets = [];
        if (!$task->asset_id && $task->location_id) {
            // Tampilkan aset fisik di lokasi tersebut + semua aset virtual/software
            $locationAssets = \App\Models\Asset::where('location_id', $task->location_id)
                                               ->orWhereNull('location_id')
                                               ->get();
        }

        return view('technician.tasks.show', compact('task', 'groups', 'locationAssets'));
    }

    // 1. CLAIM TASK — Ambil Tiket dari Antrean Grup / Pool Umum
    public function claim($id)
    {
        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        // Validasi: hanya bisa claim jika handover, handed_over, atau open
        if (!in_array($task->status, ['open', 'handover', 'handed_over'])) {
            return back()->with('error', 'Tugas ini tidak bisa diambil (Status tidak valid).');
        }

        // Guard: teknisi hanya bisa klaim tiket dari grupnya sendiri atau dari Pool Umum
        if ($task->assigned_group_id !== null && $task->assigned_group_id !== $user->technician_group_id) {
            return back()->with('error', 'Tiket ini hanya bisa diklaim oleh grup yang ditugaskan.');
        }

        DB::transaction(function () use ($task, $user) {
            $task->executed_by_user_id = $user->id;  // Tandai siapa yang mengambil
            $task->status              = 'in_progress';
            $task->save();

            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id'       => $user->id,
                'action'        => 'picked_up',
                'description'   => 'Mengambil tugas dari antrean grup (Claim).',
            ]);
        });

        return redirect()
            ->route('technician.tasks.index', ['tab' => 'my_tasks'])
            ->with('success', 'Tugas berhasil diambil.');
    }

    // 1.5 START TASK (Mulai Kerjakan - untuk Assigned)
    public function start($id)
    {
        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        if ($task->technician_id != $user->id && $task->executed_by_user_id != $user->id) {
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
        if ($task->technician_id != $user->id && $task->executed_by_user_id != $user->id) {
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

    // 3. HANDOVER TASK — Serahkan ke Grup Lain
    public function handover(Request $request, $id)
    {
        $request->validate([
            'to_group_id' => 'required|exists:technician_groups,id', // Grup tujuan wajib dipilih
            'notes'       => 'required|string|min:10|max:1000',      // Catatan progres wajib diisi
            'photos'      => 'nullable|array|max:5',
            'photos.*'    => 'image|mimes:jpeg,png,jpg|max:5120',
            'asset_id'    => 'nullable|exists:assets,id',
        ]);

        $task = WorkOrder::findOrFail($id);
        $user = auth()->user();

        // Guard: hanya teknisi yang mengerjakan tiket ini yang bisa handover
        if ($task->executed_by_user_id !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk handover tugas ini.');
        }

        // Guard: tidak bisa handover ke grup diri sendiri
        if ((int) $request->to_group_id === $user->technician_group_id) {
            return back()->with('error', 'Tidak bisa handover ke grup Anda sendiri.');
        }

        DB::transaction(function () use ($task, $user, $request) {
            // Upload foto bukti progres (opsional)
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('handover-photos', 'public');
                }
            }

            // Jika tiket belum punya aset dan user isi, identifikasi sekarang
            if (!$task->asset_id && $request->filled('asset_id')) {
                $task->asset_id = $request->asset_id;
            }

            $fromGroupId = $user->technician_group_id; // Grup pengirim

            // 1. Pindahkan tiket ke grup tujuan
            $task->assigned_group_id   = (int) $request->to_group_id;
            $task->executed_by_user_id = null;       // Lepas dari eksekutor lama
            $task->status              = 'handed_over'; // Status baru untuk handover antar-grup
            $task->save();

            // 2. Insert audit trail ke tabel work_order_handovers
            WorkOrderHandover::create([
                'work_order_id'          => $task->id,
                'from_group_id'          => $fromGroupId,
                'to_group_id'            => (int) $request->to_group_id,
                'handed_over_by_user_id' => $user->id,
                'notes'                  => $request->notes,
            ]);

            // 3. Catat di history timeline tiket
            WorkOrderHistory::create([
                'work_order_id' => $task->id,
                'user_id'       => $user->id,
                'action'        => 'handover',
                'description'   => 'Handover ke Grup: ' . $request->to_group_id . '. Catatan: ' . $request->notes,
                'photos'        => $photoPaths,
            ]);
        });

        return redirect()
            ->route('technician.tasks.index')
            ->with('success', 'Tiket berhasil di-handover ke grup tujuan.');
    }
}
