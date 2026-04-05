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
        if ($maintenance->status === 'OPEN' || $maintenance->status === 'pending') {
            $maintenance->update([
                'status' => 'IN_PROGRESS',
                'technician_id' => auth()->id(),
            ]);
        }

        // REDIRECT to Unified Form if this is a Grouped/Location-based Task
        if ($maintenance->target_asset_ids && count($maintenance->target_asset_ids) > 0) {
            return redirect()->route('technician.locations.maintenance.inspect', $maintenance->id);
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
            'photos' => 'required_if:has_issue,true|nullable|array|max:5',
            'photos.*' => 'image|max:5120', // 5MB Max per photo
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

                // Fetch template items to identify headers
                $templateItems = \App\Models\ChecklistItem::where('checklist_template_id', $templateId)->get();
                $headerItemIds = $templateItems->where('type', 'header')->pluck('id')->toArray();

                // 0. Filter Header Items from Answers
                $filteredAnswers = collect($request->answers)->reject(function ($value, $key) use ($headerItemIds) {
                    return in_array($key, $headerItemIds);
                })->toArray();

                // Upload Photos if exists
                $photoPaths = [];
                if ($request->hasFile('photos')) {
                    $files = $request->file('photos');
                    if (!is_array($files)) $files = [$files];
                    foreach ($files as $file) {
                        if ($file && $file->isValid()) {
                            $photoPaths[] = $file->store('maintenance-evidence', 'public');
                        }
                    }
                }
                
                // 1. Save Patrol Log
                $patrolLog = PatrolLog::create([
                    'technician_id' => auth()->id(),
                    'asset_id' => $maintenance->asset_id,
                    'location_id' => $maintenance->asset->location_id,
                    'checklist_template_id' => $templateId,
                    'inspection_data' => json_encode($filteredAnswers),
                    'status' => $request->has_issue ? 'issue_found' : 'normal',
                    'notes' => $request->notes ?? $request->input('notes'),
                    'photos' => $photoPaths,
                    'shift_id' => auth()->user()->shift_id,
                ]);
                
                // 2. Update Maintenance Status
                $maintenance->update([
                    'status' => 'COMPLETED',
                    'result_data' => $filteredAnswers,
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
                        'photos_before' => $photoPaths,
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
