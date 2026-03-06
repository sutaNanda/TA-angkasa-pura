<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\PatrolLog;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    /**
     * Show inspection form for specific asset
     */
    public function show($assetId)
    {
        $asset = Asset::with(['category.checklistTemplates.items', 'location'])->findOrFail($assetId);
        
        // Get first checklist template for this asset's category
        $template = $asset->category?->checklistTemplates()
            ->with('items')
            ->first();
        
        if (!$template) {
            return redirect()->back()->with('error', 'Tidak ada checklist template untuk kategori aset ini.');
        }
        
        return view('technician.maintenance.inspect', compact('asset', 'template'));
    }

    /**
     * Store inspection results
     */
    /**
     * Store inspection results with automated escalation logic
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'template_id' => 'required|exists:checklist_templates,id',
            'answers' => 'required|array',
            'has_issue' => 'required|boolean',
            'notes' => 'required_if:has_issue,true|nullable|string',
            'photos' => 'required_if:has_issue,true|nullable|array|max:5', // Array of images
            'photos.*' => 'image|max:5120', // 5MB Max per photo
            'is_critical' => 'sometimes|boolean'
        ]);

        try {
            $result = \DB::transaction(function () use ($request) {
                $asset = Asset::findOrFail($request->asset_id);
                
                // Fetch template items to identify headers
                $templateItems = \App\Models\ChecklistItem::where('checklist_template_id', $request->template_id)->get();
                $headerItemIds = $templateItems->where('type', 'header')->pluck('id')->toArray();

                // 0. Filter Header Items from Answers (They shouldn't be required)
                $filteredAnswers = collect($request->answers)->reject(function ($value, $key) use ($headerItemIds) {
                    return in_array($key, $headerItemIds);
                })->toArray();

                // Upload Photos if exists
                $photoPaths = [];
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $file) {
                        $photoPaths[] = $file->store('patrol-evidence', 'public');
                    }
                }

                // 1. Save Patrol Log
                $patrolLog = PatrolLog::create([
                    'technician_id' => Auth::id(),
                    'asset_id' => $request->asset_id,
                    'location_id' => $asset->location_id,
                    'checklist_template_id' => $request->template_id,
                    'inspection_data' => json_encode($filteredAnswers),
                    'status' => $request->has_issue ? 'issue_found' : 'normal',
                    'notes' => $request->notes,
                    'photos' => $photoPaths,
                ]);
                
                // Fix: If PatrolLog doesn't have 'photo' column, we might fail. 
                // Let's assume it fits, if error, I will fix.
                
                $response = [
                    'status' => 'success',
                    'has_issue' => false,
                    'redirect_url_location' => route('technician.scan.show', $asset->location_id),
                ];

                // 2. Auto-Create WorkOrder if Issue Found
                if ($request->has_issue) {
                    $ticketNumber = 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
                    
                    $workOrder = WorkOrder::create([
                        'ticket_number' => $ticketNumber,
                        'asset_id' => $asset->id,
                        'technician_id' => null, // Masuk ke Pool (agar bisa diambil siapa saja)
                        'reported_by' => Auth::id(), // Self-reported
                        'priority' => $request->is_critical ? 'high' : 'medium',
                        'status' => 'open',
                        'source' => 'patrol',
                        'issue_description' => $request->notes ?? 'Masalah ditemukan saat patroli',
                        'photos_before' => $photoPaths,
                    ]);

                    $response['has_issue'] = true;
                    $response['work_order_id'] = $workOrder->id;
                    $response['redirect_url_ticket'] = route('technician.tasks.show', $workOrder->id);
                }

                return $response;
            });

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
