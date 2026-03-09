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
        $maintenance->load(['location.assets']);
        
        $items = collect(); // Wadah kosong untuk menampung SEMUA pertanyaan
        $templateName = 'Inspeksi Area Kesatuan';
        $primaryTemplateId = null;

        // 1. Cek apakah ada Template langsung di tabel Maintenance
        if ($maintenance->checklist_template_id) {
            $template = \App\Models\ChecklistTemplate::with('items')->find($maintenance->checklist_template_id);
            if ($template) {
                $items = $template->items;
                $templateName = $template->name;
                $primaryTemplateId = $template->id;
            }
        } 
        // 2. Cek ke Maintenance Plan (Sistem Multi-Template / Kategori)
        elseif ($maintenance->maintenancePlan) {
            $templateName = $maintenance->maintenancePlan->name;
            
            if (isset($maintenance->maintenancePlan->checklist_template_id) && $maintenance->maintenancePlan->checklist_template_id) {
                $template = \App\Models\ChecklistTemplate::with('items')->find($maintenance->maintenancePlan->checklist_template_id);
                if ($template) {
                    $items = $template->items;
                    $primaryTemplateId = $template->id;
                }
            } elseif (isset($maintenance->maintenancePlan->template_configs)) {
                $configs = is_string($maintenance->maintenancePlan->template_configs) 
                            ? json_decode($maintenance->maintenancePlan->template_configs, true) 
                            : $maintenance->maintenancePlan->template_configs;
                
                if (is_array($configs)) {
                    // SULAPNYA DI SINI: Looping semua template, dan GABUNGKAN SEMUA PERTANYAANNYA!
                    foreach ($configs as $config) {
                        if (isset($config['template_id'])) {
                            $template = \App\Models\ChecklistTemplate::with('items')->find($config['template_id']);
                            if ($template && $template->items) {
                                $items = $items->merge($template->items);
                                if (!$primaryTemplateId) {
                                    $primaryTemplateId = $template->id; // Simpan ID template pertama untuk di log database
                                }
                            }
                        }
                    }
                }
            }
        }

        // Jika setelah digabung ternyata kosong, tolak.
        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Tugas ini tidak memiliki satupun item SOP. Silakan atur Template di Jadwal Maintenance.');
        }

        // 3. Ambil daftar aset HANYA untuk Dropdown pelaporan masalah
        $assets = $maintenance->location ? $maintenance->location->assets : \App\Models\Asset::all();

        return view('technician.maintenance.inspect_unified', compact('maintenance', 'items', 'templateName', 'primaryTemplateId', 'assets'));
    }

public function storeMaintenance(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'answers' => 'required|array',
            'notes' => 'nullable|array', 
            'failed_asset_ids' => 'nullable|array', 
            'global_notes' => 'nullable|string', 
            'photos' => 'nullable|array',
            'primary_template_id' => 'nullable' // Menerima ID template dari form
        ]);

        DB::beginTransaction();
        try {
            $hasIssue = false;
            $workOrdersCreated = [];
            
            // Loop mengecek jawaban
            foreach ($request->answers as $itemId => $answer) {
                if ($answer === 'fail' || $answer === 'broken' || $answer === 'no') {
                    $hasIssue = true;
                    
                    $selectedAssetId = $request->failed_asset_ids[$itemId] ?? null;
                    if ($selectedAssetId === 'area_general') {
                        $selectedAssetId = null; 
                    }

                    $workOrder = \App\Models\WorkOrder::create([
                        'ticket_number' => 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                        'asset_id' => $selectedAssetId, 
                        'location_id' => $maintenance->location_id,
                        'technician_id' => null,
                        'reporter_id' => Auth::id(),
                        'priority' => 'medium',
                        'status' => 'open',
                        'source' => 'patrol',
                        'issue_description' => $request->notes[$itemId] ?? ($request->global_notes ?? 'Masalah ditemukan saat inspeksi area.'),
                        'maintenance_id' => $maintenance->id,
                    ]);
                    
                    $workOrdersCreated[] = $workOrder->id;
                }
            }

            // Simpan Foto Bukti Global
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('maintenance-evidence', 'public');
                }
            }

            // Buat 1 Log Patroli
            $patrolLog = \App\Models\PatrolLog::create([
                'technician_id' => Auth::id(),
                'asset_id' => $maintenance->asset_id,
                'location_id' => $maintenance->location_id,
                'checklist_template_id' => $request->primary_template_id ?? $maintenance->checklist_template_id,
                'work_order_id' => $workOrdersCreated[0] ?? null, // LINK KE TIKET PERTAMA
                'inspection_data' => json_encode([
                    'answers' => $request->answers,
                    'notes' => $request->notes,
                    'failed_assets' => $request->failed_asset_ids ?? []
                ]),
                'status' => $hasIssue ? 'issue_found' : 'normal',
                'notes' => $request->global_notes,
                'photos' => $photoPaths,
            ]);

            // Tandai Maintenance Selesai
            $maintenance->update([
                'status' => 'COMPLETED',
                'technician_id' => Auth::id(),
                'result_data' => json_encode(['answers' => $request->answers, 'notes' => $request->notes]), 
            ]);

            DB::commit();
            
            $response = [
                'status' => 'success',
                'has_issue' => $hasIssue,
                'redirect_url_location' => route('technician.dashboard'),
            ];

            if ($hasIssue && count($workOrdersCreated) > 0) {
                $response['work_order_id'] = $workOrdersCreated[0];
                $response['redirect_url_ticket'] = route('technician.tasks.show', $workOrdersCreated[0]);
            }

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage() . ' di baris ' . $e->getLine()], 500);
        }
    }

    public function inspectMaintenanceGroup(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        if (empty($ids) || count($ids) == 0 || $ids[0] == '') {
             return redirect()->route('technician.dashboard')->with('error', 'Tidak ada tugas yang dipilih.');
        }

        $maintenances = Maintenance::whereIn('id', $ids)
             ->with(['location.assets', 'asset.location', 'asset.parentAsset', 'maintenancePlan'])
             ->get();

        if ($maintenances->isEmpty()) {
             return redirect()->route('technician.dashboard')->with('error', 'Tugas tidak ditemukan.');
        }

        // Get first valid location
        $primaryLocation = null;
        foreach($maintenances as $m) {
            if ($m->location) { $primaryLocation = $m->location; break; }
            if ($m->asset && $m->asset->location) { $primaryLocation = $m->asset->location; break; }
            if ($m->asset && $m->asset->parentAsset && $m->asset->parentAsset->location) { $primaryLocation = $m->asset->parentAsset->location; break; }
        }

        $groupedTemplates = [];
        $primaryTemplateId = null;
        $processedTemplateIds = [];

        foreach ($maintenances as $maintenance) {
            $items = collect();
            $categoryName = 'Unknown Category';

            if ($maintenance->maintenancePlan) {
                 $categoryName = $maintenance->maintenancePlan->name;
                 if (isset($maintenance->maintenancePlan->template_configs)) {
                     $configs = is_string($maintenance->maintenancePlan->template_configs) 
                         ? json_decode($maintenance->maintenancePlan->template_configs, true) 
                         : $maintenance->maintenancePlan->template_configs;
                     
                     if (is_array($configs)) {
                         foreach ($configs as $config) {
                             if (isset($config['template_id'])) {
                                 if (in_array($config['template_id'], $processedTemplateIds)) {
                                     continue;
                                 }
                                 $processedTemplateIds[] = $config['template_id'];
                                 
                                 $template = \App\Models\ChecklistTemplate::with('items')->find($config['template_id']);
                                 if ($template && $template->items) {
                                     $items = $items->merge($template->items);
                                     if (!$primaryTemplateId) {
                                         $primaryTemplateId = $template->id;
                                     }
                                 }
                             }
                         }
                     }
                 }
            } elseif ($maintenance->checklist_template_id) {
                 if (!in_array($maintenance->checklist_template_id, $processedTemplateIds)) {
                     $processedTemplateIds[] = $maintenance->checklist_template_id;
                     $template = \App\Models\ChecklistTemplate::with('items')->find($maintenance->checklist_template_id);
                     if ($template) {
                         $items = $template->items;
                         $categoryName = $template->name;
                         if (!$primaryTemplateId) $primaryTemplateId = $template->id;
                     }
                 }
            }

            if ($items->isNotEmpty()) {
                 $groupedTemplates[] = [
                     'maintenance_id' => $maintenance->id,
                     'category_name' => $categoryName,
                     'items' => $items
                 ];
            }
        }

        if (empty($groupedTemplates)) {
            return redirect()->route('technician.dashboard')->with('error', 'Tugas ini tidak memiliki satupun item SOP.');
        }

        // Assets for dropdown
        if ($primaryLocation) {
             $primaryLocation->load(['assets' => function($q) {
                  $q->with('childAssets');
             }]);
             // Combine physical assets + installed software
             $assets = collect();
             foreach ($primaryLocation->assets as $physAsset) {
                 $assets->push($physAsset);
                 if ($physAsset->childAssets) {
                     foreach ($physAsset->childAssets as $softAsset) {
                         $assets->push($softAsset);
                     }
                 }
             }
        } else {
             $assets = \App\Models\Asset::all();
        }

        $maintenanceIdsStr = implode(',', $ids);
        $templateName = 'Inspeksi Area Terpadu';
        $maintenance = $maintenances->first();

        return view('technician.maintenance.inspect_unified', compact('groupedTemplates', 'primaryTemplateId', 'assets', 'primaryLocation', 'maintenanceIdsStr', 'templateName', 'maintenance'));
    }

    public function storeMaintenanceGroup(Request $request)
    {
        $request->validate([
            'maintenance_ids' => 'required|string',
            'answers' => 'required|array',
            'notes' => 'nullable|array', 
            'failed_asset_ids' => 'nullable|array', 
            'global_notes' => 'nullable|string', 
            'photos' => 'nullable|array',
            'primary_template_id' => 'nullable',
            'location_id' => 'nullable'
        ]);

        $ids = explode(',', $request->maintenance_ids);

        DB::beginTransaction();
        try {
            $hasIssue = false;
            $workOrdersCreated = [];
            
            // Loop mengecek jawaban
            foreach ($request->answers as $itemId => $answer) {
                if ($answer === 'fail' || $answer === 'broken' || $answer === 'no') {
                    $hasIssue = true;
                    
                    $selectedAssetId = $request->failed_asset_ids[$itemId] ?? null;
                    if ($selectedAssetId === 'area_general') {
                        $selectedAssetId = null; 
                    }

                    $workOrder = \App\Models\WorkOrder::create([
                        'ticket_number' => 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                        'asset_id' => $selectedAssetId, 
                        'location_id' => $request->location_id,
                        'technician_id' => null,
                        'reporter_id' => Auth::id(),
                        'priority' => 'medium',
                        'status' => 'open',
                        'source' => 'patrol',
                        'issue_description' => $request->notes[$itemId] ?? ($request->global_notes ?? 'Masalah ditemukan saat inspeksi area.'),
                        'maintenance_id' => $ids[0] ?? null, 
                    ]);
                    
                    $workOrdersCreated[] = $workOrder->id;
                }
            }

            // Simpan Foto Bukti Global
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('maintenance-evidence', 'public');
                }
            }

            // Buat 1 Log Patroli
            $patrolLog = \App\Models\PatrolLog::create([
                'technician_id' => Auth::id(),
                'asset_id' => null,
                'location_id' => $request->location_id,
                'checklist_template_id' => $request->primary_template_id, // Gunakan ID template utama
                'work_order_id' => $workOrdersCreated[0] ?? null, // LINK KE TIKET PERTAMA
                'inspection_data' => json_encode([
                    'answers' => $request->answers,
                    'notes' => $request->notes,
                    'failed_assets' => $request->failed_asset_ids ?? []
                ]),
                'status' => $hasIssue ? 'issue_found' : 'normal',
                'notes' => $request->global_notes,
                'photos' => $photoPaths,
            ]);

            // Tandai Semua Maintenance Selesai
            Maintenance::whereIn('id', $ids)->update([
                'status' => 'COMPLETED',
                'technician_id' => Auth::id(),
                'result_data' => json_encode(['answers' => $request->answers, 'notes' => $request->notes]), 
            ]);

            DB::commit();
            
            $response = [
                'status' => 'success',
                'has_issue' => $hasIssue,
                'redirect_url_location' => route('technician.dashboard'),
            ];

            if ($hasIssue && count($workOrdersCreated) > 0) {
                $response['work_order_id'] = $workOrdersCreated[0];
                $response['redirect_url_ticket'] = route('technician.tasks.show', $workOrdersCreated[0]);
            }

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage() . ' di baris ' . $e->getLine()], 500);
        }
    }
}