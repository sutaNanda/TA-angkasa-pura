<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\PatrolLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LkController extends Controller
{
    /**
     * Show work order creation form
     */
    public function create(Request $request)
    {
        $assetId = $request->query('asset_id');
        $patrolLogId = $request->query('patrol_log_id');
        
        $asset = Asset::with('location')->findOrFail($assetId);
        $patrolLog = $patrolLogId ? PatrolLog::find($patrolLogId) : null;
        
        return view('technician.lk.create', compact('asset', 'patrolLog'));
    }

    /**
     * Store new work order
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'issue_description' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high',
            'initial_photo' => 'nullable|image|max:5120', // 5MB max
            'patrol_log_id' => 'nullable|exists:patrol_logs,id',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('initial_photo')) {
            $photoPath = $request->file('initial_photo')->store('work-orders/initial', 'public');
        }

        // Generate ticket number
        $ticketNumber = 'WO-' . date('Ymd') . '-' . str_pad(WorkOrder::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        // Create work order
        $workOrder = WorkOrder::create([
            'ticket_number' => $ticketNumber,
            'asset_id' => $request->asset_id,
            'reporter_id' => Auth::id(),
            'issue_description' => $request->issue_description,
            'priority' => $request->priority,
            'status' => 'open', // Available for any technician to take
            'initial_photo' => $photoPath,
        ]);

        // Link to patrol log if exists
        if ($request->patrol_log_id) {
            $patrolLog = PatrolLog::find($request->patrol_log_id);
            if ($patrolLog) {
                $patrolLog->update(['work_order_id' => $workOrder->id]);
            }
        }

        return redirect()->route('technician.tasks.show', $workOrder->id)
            ->with('success', 'Laporan Kegiatan berhasil dibuat. Anda dapat mengambil tugas ini atau biarkan teknisi lain yang mengerjakannya.');
    }
}
