<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;
use App\Models\PatrolLog;

class MaintenanceController extends Controller
{
    /**
     * Start a maintenance task
     */
    public function start($maintenanceId)
    {
        $maintenance = Maintenance::with([
            'asset.category',
            'checklistTemplate.items',
            'maintenanceSchedule'
        ])->findOrFail($maintenanceId);
        
        // Update status to IN_PROGRESS if still OPEN
        if ($maintenance->status === 'OPEN') {
            $maintenance->update([
                'status' => 'IN_PROGRESS',
                'technician_id' => auth()->id(),
            ]);
        }
        
        return view('technician.maintenance.inspect', [
            'maintenance' => $maintenance,
            'asset' => $maintenance->asset,
            'template' => $maintenance->checklistTemplate,
        ]);
    }
    
    /**
     * Complete a maintenance task
     */
    /**
     * Complete a maintenance task
     */
    public function complete(Request $request, $maintenanceId)
    {
        $request->validate([
            'answers' => 'required|array',
            'has_issue' => 'required|boolean',
            'notes' => 'required_if:has_issue,true|nullable|string',
            'photo' => 'required_if:has_issue,true|nullable|image|max:5120', // 5MB Max
            'is_critical' => 'sometimes|boolean'
        ]);

        try {
            $result = \DB::transaction(function () use ($request, $maintenanceId) {
                $maintenance = Maintenance::with('asset')->findOrFail($maintenanceId);

                // Resolve Template ID (Fallback for legacy data)
                $templateId = $maintenance->checklist_template_id;
                if (!$templateId) {
                    // Try finding by Category
                    $template = \App\Models\ChecklistTemplate::where('category_id', $maintenance->asset->category_id)->first();
                    $templateId = $template ? $template->id : null;
                }

                if (!$templateId) {
                    throw new \Exception("Template Checklist tidak ditemukan untuk aset ini (ID: {$maintenance->asset_id}). Hubungi Admin.");
                }

                // Upload Photo if exists
                $photoPath = null;
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('maintenance-evidence', 'public');
                }
                
                // 1. Save Patrol Log
                $patrolLog = PatrolLog::create([
                    'technician_id' => auth()->id(),
                    'asset_id' => $maintenance->asset_id,
                    'location_id' => $maintenance->asset->location_id,
                    'checklist_template_id' => $templateId,
                    'inspection_data' => json_encode($request->answers),
                    'status' => $request->has_issue ? 'issue_found' : 'normal',
                    'notes' => $request->notes ?? $request->input('notes'), // Handle potential null
                    'photo' => $photoPath,
                    // 'work_order_id' will be updated if ticket created
                ]);
                
                // 2. Update Maintenance Status
                $maintenance->update([
                    'status' => 'COMPLETED',
                    'result_data' => $request->answers,
                    'notes' => $request->notes ?? null,
                    // Ideally link patrol log here too if column existed, but Maintenance is legacy/PM specific
                ]);

                $response = [
                    'status' => 'success',
                    'has_issue' => false,
                    'redirect_url_location' => route('technician.dashboard'),
                ];

                // 3. Auto-Create WorkOrder if Issue Found
                if ($request->has_issue) {
                    $ticketNumber = 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
                    
                    $workOrder = \App\Models\WorkOrder::create([
                        'ticket_number' => $ticketNumber,
                        'asset_id' => $maintenance->asset_id,
                        'technician_id' => null, // Masuk ke Pool, bukan langsung assign
                        'reporter_id' => auth()->id(), // Self-reported
                        'priority' => $request->is_critical ? 'high' : 'medium',
                        'status' => 'open',
                        'source' => 'patrol', // Or 'maintenance'
                        'issue_description' => $request->notes ?? 'Masalah ditemukan saat maintenance rutin',
                        'initial_photo' => $photoPath,
                        'photo_before' => $photoPath,
                        'maintenance_id' => $maintenance->id, // Link to this maintenance schedule
                    ]);
                    
                    // Link back in PatrolLog
                    $patrolLog->update(['work_order_id' => $workOrder->id]);

                    $response['has_issue'] = true;
                    $response['work_order_id'] = $workOrder->id;
                    $response['redirect_url_ticket'] = route('technician.tasks.show', $workOrder->id);
                }

                return $response;
            });

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::channel('single')->error('Maintenance Complete Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            file_put_contents(storage_path('logs/debug_error.log'), $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
