<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\PatrolLog;
use App\Models\WorkOrder;
use App\Models\ChecklistItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LocationInspectionController extends Controller
{
    public function inspect($locationId)
    {
        if ($locationId == 0) {
            $location = new Location();
            $location->id = 0;
            $location->name = 'Virtual / Software';
            
            $assets = Asset::whereNull('location_id')
                ->with(['category.checklistTemplates.items'])
                ->get();
        } else {
            $location = Location::findOrFail($locationId);
            $location->load(['assets' => function($query) {
                $query->with(['category.checklistTemplates.items']);
            }]);
            $assets = $location->assets;
        }

        $assets = $assets->filter(function($asset) {
            return $asset->category && $asset->category->checklistTemplates->isNotEmpty();
        });

        if ($assets->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada aset dengan template checklist di lokasi ini.');
        }

        return view('technician.locations.inspect', compact('location', 'assets'));
    }

    public function store(Request $request, $locationId)
    {
        $request->validate([
            'answers' => 'required|array',
            'global_notes' => 'nullable|array',
            'photos' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $workOrdersCreated = [];
            $hasAnyIssue = false;

            foreach ($request->answers as $assetId => $itemAnswers) {
                $asset = Asset::findOrFail($assetId);
                $template = $asset->category->checklistTemplates()->first();
                if (!$template) continue;

                $headerItemIds = $template->items->where('type', 'header')->pluck('id')->toArray();
                
                $filteredAnswers = collect($itemAnswers)->reject(function($value, $key) use ($headerItemIds) {
                    return in_array($key, $headerItemIds);
                })->toArray();

                $hasIssue = false;
                foreach ($filteredAnswers as $answer) {
                    if ($answer === 'fail' || $answer === 'broken' || $answer === 'no') {
                        $hasIssue = true;
                        $hasAnyIssue = true;
                        break;
                    }
                }

                $photoPaths = [];
                if ($request->hasFile("photos.{$assetId}")) {
                    foreach ($request->file("photos.{$assetId}") as $file) {
                        $photoPaths[] = $file->store('patrol-evidence', 'public');
                    }
                }

                $notes = $request->global_notes[$assetId] ?? null;

                $patrolLog = PatrolLog::create([
                    'technician_id' => Auth::id(),
                    'asset_id' => $assetId,
                    'location_id' => $locationId == 0 ? null : $locationId,
                    'checklist_template_id' => $template->id,
                    'inspection_data' => $filteredAnswers,
                    'status' => $hasIssue ? 'issue_found' : 'normal',
                    'notes' => $notes,
                    'photos' => $photoPaths,
                ]);

                if ($hasIssue) {
                    $workOrder = WorkOrder::create([
                        'ticket_number' => 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                        'asset_id' => $assetId,
                        'technician_id' => null, // Pool
                        'reported_by' => Auth::id(),
                        'priority' => 'medium',
                        'status' => 'open', // Terbuka
                        'source' => 'patrol',
                        'issue_description' => $notes ?? 'Masalah ditemukan saat inspeksi area.',
                        'photos_before' => $photoPaths,
                    ]);

                    if(\Schema::hasColumn('patrol_logs', 'work_order_id')) {
                         $patrolLog->update(['work_order_id' => $workOrder->id]);
                    }
                    $workOrdersCreated[] = $workOrder->id;
                }
            }

            DB::commit();

            // RESPONSE JSON UNTUK DITANGKAP OLEH AJAX DI BLADE
            $response = [
                'status' => 'success',
                'has_issue' => $hasAnyIssue,
                'redirect_url_location' => route('technician.dashboard'), // Kalo pilih "Tidak/Lanjut Patroli" arahkan ke dashboard
            ];

            if ($hasAnyIssue && count($workOrdersCreated) > 0) {
                $response['work_order_id'] = $workOrdersCreated[0];
                $response['redirect_url_ticket'] = route('technician.tasks.show', $workOrdersCreated[0]); // Kalo pilih "Perbaiki Sekarang" arahkan ke tiket
            }

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Location Inspection Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan inspeksi (Lokasi): ' . $e->getMessage()], 500);
        }
    }

    public function inspectMaintenance(Maintenance $maintenance)
    {
        $maintenance->load(['location', 'maintenancePlan']);
        
        if (!$maintenance->target_asset_ids || empty($maintenance->target_asset_ids)) {
            return redirect()->back()->with('error', 'Tugas maintenance ini tidak memiliki aset target.');
        }

        $assets = Asset::whereIn('id', $maintenance->target_asset_ids)->with(['category'])->get();

        $planConfigs = collect($maintenance->maintenancePlan->template_configs ?? []);
        $templates = [];
        foreach ($planConfigs as $config) {
            $templates[$config['category_id']] = \App\Models\ChecklistTemplate::with('items')->find($config['template_id']);
        }

        return view('technician.maintenance.inspect_unified', compact('maintenance', 'assets', 'templates'));
    }

    public function storeMaintenance(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'answers' => 'required|array',
            'global_notes' => 'nullable|array',
            'photos' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $planConfigs = collect($maintenance->maintenancePlan->template_configs ?? []);
            $workOrdersCreated = [];
            $hasAnyIssue = false;
            
            foreach ($request->answers as $assetId => $itemAnswers) {
                $asset = Asset::findOrFail($assetId);
                $config = $planConfigs->firstWhere('category_id', $asset->category_id);
                if (!$config) continue;

                $template = \App\Models\ChecklistTemplate::find($config['template_id']);
                if (!$template) continue;

                $headerItemIds = ChecklistItem::where('checklist_template_id', $template->id)->where('type', 'header')->pluck('id')->toArray();

                $filteredAnswers = collect($itemAnswers)->reject(function($value, $key) use ($headerItemIds) {
                    return in_array($key, $headerItemIds);
                })->toArray();

                $hasIssue = false;
                foreach ($filteredAnswers as $answer) {
                    if ($answer === 'fail' || $answer === 'broken' || $answer === 'no') {
                        $hasIssue = true;
                        $hasAnyIssue = true;
                        break;
                    }
                }

                $photoPaths = [];
                if ($request->hasFile("photos.{$assetId}")) {
                    foreach ($request->file("photos.{$assetId}") as $file) {
                        $photoPaths[] = $file->store('maintenance-evidence', 'public');
                    }
                }

                $notes = $request->global_notes[$assetId] ?? null;

                $patrolLog = PatrolLog::create([
                    'technician_id' => Auth::id(),
                    'asset_id' => $assetId,
                    'location_id' => $maintenance->location_id,
                    'checklist_template_id' => $template->id,
                    'inspection_data' => $filteredAnswers,
                    'status' => $hasIssue ? 'issue_found' : 'normal',
                    'notes' => $notes,
                    'photos' => $photoPaths,
                ]);

                if ($hasIssue) {
                    $workOrder = WorkOrder::create([
                        'ticket_number' => 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                        'asset_id' => $assetId,
                        'technician_id' => null,
                        'reported_by' => Auth::id(),
                        'priority' => 'medium',
                        'status' => 'open',
                        'source' => 'patrol',
                        'issue_description' => $notes ?? 'Masalah ditemukan saat maintenance rutin terpadu.',
                        'photos_before' => $photoPaths,
                        'maintenance_id' => $maintenance->id,
                    ]);

                    if(\Schema::hasColumn('patrol_logs', 'work_order_id')) {
                         $patrolLog->update(['work_order_id' => $workOrder->id]);
                    }
                    $workOrdersCreated[] = $workOrder->id;
                }
            }

            $maintenance->update([
                'status' => 'COMPLETED',
                'technician_id' => Auth::id(),
                'result_data' => $request->answers, 
            ]);

            DB::commit();
            
            // RESPONSE JSON
            $response = [
                'status' => 'success',
                'has_issue' => $hasAnyIssue,
                'redirect_url_location' => route('technician.dashboard'),
            ];

            if ($hasAnyIssue && count($workOrdersCreated) > 0) {
                $response['work_order_id'] = $workOrdersCreated[0];
                $response['redirect_url_ticket'] = route('technician.tasks.show', $workOrdersCreated[0]);
            }

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Maintenance Inspection Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan inspeksi (Maintenance): ' . $e->getMessage()], 500);
        }
    }
}