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
                    'shift_id' => Auth::user()->shift_id,
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
                        'issue_description' => $notes ?? 'Masalah ditemukan saat inspeksi area.',
                        'initial_photo' => $photoPaths[0] ?? null,
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
        
        $items = collect();
        $groupedTemplates = null; // Will be set if multi-template
        $templateName = 'Inspeksi Area Kesatuan';
        $primaryTemplateId = null;

        // 1. Cek apakah ada Template langsung di tabel Maintenance
        if ($maintenance->checklist_template_id) {
            $template = \App\Models\ChecklistTemplate::with(['items', 'category'])->find($maintenance->checklist_template_id);
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
                $template = \App\Models\ChecklistTemplate::with(['items', 'category'])->find($maintenance->maintenancePlan->checklist_template_id);
                if ($template) {
                    $items = $template->items;
                    $templateName = $template->name;
                    $primaryTemplateId = $template->id;
                }
            } elseif (isset($maintenance->maintenancePlan->template_configs)) {
                $configs = is_string($maintenance->maintenancePlan->template_configs) 
                            ? json_decode($maintenance->maintenancePlan->template_configs, true) 
                            : $maintenance->maintenancePlan->template_configs;
                
                if (is_array($configs)) {
                    // Setiap template menjadi grup terpisah (bukan digabung menjadi satu list)
                    $groupedTemplates = [];
                    foreach ($configs as $config) {
                        if (isset($config['template_id'])) {
                            $template = \App\Models\ChecklistTemplate::with(['items', 'category'])->find($config['template_id']);
                            if ($template && $template->items && $template->items->isNotEmpty()) {
                                if (!$primaryTemplateId) {
                                    $primaryTemplateId = $template->id;
                                }
                                $groupedTemplates[] = [
                                    'maintenance_id' => $maintenance->id,
                                    'template_name'  => $template->name,
                                    'category_name'  => $template->category->name ?? 'Umum',
                                    'items'          => $template->items,
                                ];
                            }
                        }
                    }
                    // Jika hanya 1 template, fallback ke mode single
                    if (count($groupedTemplates) == 1) {
                        $items = $groupedTemplates[0]['items'];
                        $templateName = $groupedTemplates[0]['template_name'];
                        $groupedTemplates = null;
                    } elseif (empty($groupedTemplates)) {
                        $groupedTemplates = null;
                    }
                }
            }
        }

        // Jika setelah digabung ternyata kosong, tolak.
        if ($items->isEmpty() && empty($groupedTemplates)) {
            return redirect()->back()->with('error', 'Tugas ini tidak memiliki satupun item SOP. Silakan atur Template di Jadwal Maintenance.');
        }

        // 3. Ambil daftar aset HANYA untuk Dropdown pelaporan masalah
        $assets = $maintenance->location ? $maintenance->location->assets : \App\Models\Asset::all();

        return view('technician.maintenance.inspect_unified', compact('maintenance', 'items', 'templateName', 'primaryTemplateId', 'assets', 'groupedTemplates'));
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
            
            // Simpan Foto Bukti Global (Ditarik ke atas agar bisa dipakai oleh WorkOrder)
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('maintenance-evidence', 'public');
                }
            }

            $issuesByAsset = [];

            // Loop mengecek jawaban
            foreach ($request->answers as $itemId => $answer) {
                if ($answer === 'fail' || $answer === 'broken' || $answer === 'no') {
                    $hasIssue = true;
                    
                    $selectedAssetId = $request->failed_asset_ids[$itemId] ?? 'area_general';
                    if (empty($selectedAssetId)) $selectedAssetId = 'area_general';

                    // Cari pertanyaan untuk detail deskripsi
                    $questionText = 'Pengecekan SOP';
                    $item = \App\Models\ChecklistItem::find($itemId);
                    if ($item) $questionText = $item->question;

                    // Logic Teks Deskripsi
                    $specificNote = !empty($request->notes[$itemId]) ? $request->notes[$itemId] : 'Tidak ada keterangan spesifik';
                    $baseDesc = "- SOP: {$questionText}\n  Keterangan: {$specificNote}";

                    $issuesByAsset[$selectedAssetId][] = $baseDesc;
                }
            }

            if ($hasIssue && !empty($issuesByAsset)) {
                foreach ($issuesByAsset as $aId => $descLines) {
                    $selectedAssetId = ($aId === 'area_general') ? null : $aId;
                    
                    $finalDesc = count($descLines) > 1 
                        ? "Ditemukan " . count($descLines) . " masalah saat inspeksi:\n" . implode("\n", $descLines)
                        : implode("\n", $descLines);

                    if (!empty($request->global_notes)) {
                         $finalDesc .= "\n\nCatatan Global Laporan: " . $request->global_notes;
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
                        'issue_description' => $finalDesc,
                        'maintenance_id' => $maintenance->id,
                        'initial_photo' => $photoPaths[0] ?? null,
                    ]);
                    
                    $workOrdersCreated[] = $workOrder->id;
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
                'shift_id' => Auth::user()->shift_id,
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

        // Get first valid location — mulai dari lokasi paling langsung
        $primaryLocation = null;
        foreach($maintenances as $m) {
            // 1. location_id langsung di tabel maintenances (digunakan GenerateDailyMaintenanceTasks)
            if ($m->location) { $primaryLocation = $m->location; break; }
            // 2. Dari relasi asset
            if ($m->asset && $m->asset->location) { $primaryLocation = $m->asset->location; break; }
            if ($m->asset && $m->asset->parentAsset && $m->asset->parentAsset->location) { $primaryLocation = $m->asset->parentAsset->location; break; }
            // 3. Dari target_asset_ids (fallback untuk data yang dibuat otomatis)
            if (!empty($m->target_asset_ids)) {
                $firstAsset = \App\Models\Asset::with('location', 'parentAsset.location')->find($m->target_asset_ids[0]);
                if ($firstAsset) {
                    $loc = $firstAsset->location ?? $firstAsset->parentAsset?->location ?? null;
                    if ($loc) { $primaryLocation = $loc; break; }
                }
            }
        }

        $groupedTemplates = [];
        $primaryTemplateId = null;
        $processedTemplateIds = [];

        foreach ($maintenances as $maintenance) {
            $templateFound = false;

            // --- Sumber 1: Template dari MaintenancePlan (multi-template via template_configs) ---
            if ($maintenance->maintenancePlan && !empty($maintenance->maintenancePlan->template_configs)) {
                $configs = is_array($maintenance->maintenancePlan->template_configs)
                    ? $maintenance->maintenancePlan->template_configs
                    : json_decode($maintenance->maintenancePlan->template_configs, true);

                if (is_array($configs) && count($configs) > 0) {
                    foreach ($configs as $config) {
                        if (isset($config['template_id'])) {
                            if (in_array($config['template_id'], $processedTemplateIds)) {
                                continue; // Skip duplikat
                            }
                            $processedTemplateIds[] = $config['template_id'];

                            $template = \App\Models\ChecklistTemplate::with(['items', 'category'])->find($config['template_id']);
                            if ($template && $template->items && $template->items->isNotEmpty()) {
                                if (!$primaryTemplateId) $primaryTemplateId = $template->id;
                                $groupedTemplates[] = [
                                    'maintenance_id' => $maintenance->id,
                                    'template_name'  => $template->name,
                                    'category_name'  => $template->category->name ?? 'Umum',
                                    'items'          => $template->items,
                                ];
                                $templateFound = true;
                            }
                        }
                    }
                }
            }

            // --- Sumber 2: Template langsung di tabel Maintenance (dicek selalu, bukan hanya jika tidak ada plan) ---
            if (!$templateFound && $maintenance->checklist_template_id) {
                if (!in_array($maintenance->checklist_template_id, $processedTemplateIds)) {
                    $processedTemplateIds[] = $maintenance->checklist_template_id;
                    $template = \App\Models\ChecklistTemplate::with(['items', 'category'])->find($maintenance->checklist_template_id);
                    if ($template && $template->items && $template->items->isNotEmpty()) {
                        if (!$primaryTemplateId) $primaryTemplateId = $template->id;
                        $groupedTemplates[] = [
                            'maintenance_id' => $maintenance->id,
                            'template_name'  => $template->name,
                            'category_name'  => $template->category->name ?? 'Umum',
                            'items'          => $template->items,
                        ];
                        $templateFound = true;
                    }
                }
            }

            // --- Sumber 3: Fallback dari asset_id langsung ---
            if (!$templateFound && $maintenance->asset && $maintenance->asset->category) {
                $maintenance->asset->load('category.checklistTemplates.items');
                $assetTemplates = $maintenance->asset->category->checklistTemplates ?? collect();
                foreach ($assetTemplates as $template) {
                    if (in_array($template->id, $processedTemplateIds)) continue;
                    if ($template->items && $template->items->isNotEmpty()) {
                        $processedTemplateIds[] = $template->id;
                        if (!$primaryTemplateId) $primaryTemplateId = $template->id;
                        $groupedTemplates[] = [
                            'maintenance_id' => $maintenance->id,
                            'template_name'  => $template->name,
                            'category_name'  => $maintenance->asset->category->name ?? 'Umum',
                            'items'          => $template->items,
                        ];
                        $templateFound = true;
                    }
                }
            }

            // --- Sumber 4: Fallback dari target_asset_ids (digunakan oleh GenerateDailyMaintenanceTasks) ---
            // Maintenance records yang dibuat otomatis menyimpan aset di target_asset_ids, bukan asset_id
            if (!$templateFound && !empty($maintenance->target_asset_ids)) {
                $targetAssets = \App\Models\Asset::with(['category.checklistTemplates.items'])
                    ->whereIn('id', $maintenance->target_asset_ids)
                    ->get();

                // Tentukan template dari plan->template_configs berdasarkan kategori aset
                $planConfigs = [];
                if ($maintenance->maintenancePlan && !empty($maintenance->maintenancePlan->template_configs)) {
                    $planConfigs = is_array($maintenance->maintenancePlan->template_configs)
                        ? $maintenance->maintenancePlan->template_configs
                        : json_decode($maintenance->maintenancePlan->template_configs, true);
                }
                $planTemplateIdsByCategoryId = collect($planConfigs)->keyBy('category_id')->map(fn($c) => $c['template_id'] ?? null);

                $processedCategoryIds = [];
                foreach ($targetAssets as $asset) {
                    if (!$asset->category) continue;
                    $categoryId = $asset->category_id;
                    if (in_array($categoryId, $processedCategoryIds)) continue;
                    $processedCategoryIds[] = $categoryId;

                    // Cari template spesifik dari plan config, fallback ke template pertama di kategori
                    $specificTemplateId = $planTemplateIdsByCategoryId[$categoryId] ?? null;
                    $templates = $asset->category->checklistTemplates;

                    if ($specificTemplateId) {
                        $templates = $templates->sortByDesc(fn($t) => $t->id === $specificTemplateId);
                    }

                    foreach ($templates as $template) {
                        if (in_array($template->id, $processedTemplateIds)) continue;
                        if ($template->items && $template->items->isNotEmpty()) {
                            $processedTemplateIds[] = $template->id;
                            if (!$primaryTemplateId) $primaryTemplateId = $template->id;
                            $groupedTemplates[] = [
                                'maintenance_id' => $maintenance->id,
                                'template_name'  => $template->name,
                                'category_name'  => $asset->category->name ?? 'Umum',
                                'items'          => $template->items,
                            ];
                            $templateFound = true;
                            break; // Satu template per kategori
                        }
                    }
                }
            }
        }

        if (empty($groupedTemplates)) {
            $planName = $maintenances->first()->maintenancePlan->name ?? '';
            $errorMsg = $planName
                ? 'Rencana maintenance ' . $planName . ' belum memiliki SOP/Checklist. Hubungi Admin untuk mengatur template checklist.'
                : 'Jadwal ini belum memiliki SOP/Checklist. Hubungi Admin untuk mengatur template checklist.';
            return redirect()->route('technician.dashboard')->with('error', $errorMsg);
        }

        // ---- Build Assets By Category (untuk Mass Triage Grid di view) ----
        // Kumpulkan SEMUA aset yang relevan: dari location + dari target_asset_ids
        $allAssetIds = collect();

        // A. Dari location
        if ($primaryLocation) {
            $primaryLocation->load(['assets.category', 'assets.childAssets.category']);
            foreach ($primaryLocation->assets as $physAsset) {
                $allAssetIds->push($physAsset->id);
                if ($physAsset->childAssets) {
                    foreach ($physAsset->childAssets as $child) {
                        $allAssetIds->push($child->id);
                    }
                }
            }
        }

        // B. Dari target_asset_ids di semua maintenance record
        foreach ($maintenances as $m) {
            if (!empty($m->target_asset_ids)) {
                foreach ($m->target_asset_ids as $aid) {
                    $allAssetIds->push($aid);
                }
            }
        }

        $allAssetIds = $allAssetIds->unique()->values();

        $allAssets = \App\Models\Asset::with('category')
            ->whereIn('id', $allAssetIds)
            ->orderBy('name')
            ->get();

        // Group by category_id untuk dipakai di Mass Triage Grid
        $assetsByCategory = $allAssets->groupBy('category_id')->map(fn($group) => $group->values());

        // Juga simpan flat list untuk dropdown lama
        $assets = $allAssets;

        // Tambahkan category_id ke setiap group template agar view bisa korelasikan
        foreach ($groupedTemplates as &$grp) {
            if (!isset($grp['category_id'])) {
                // Cari category_id dari template
                $tpl = \App\Models\ChecklistTemplate::find($grp['items']->first()->checklist_template_id ?? null);
                // Fallback: cari dari nama kategori
                $cat = \App\Models\Category::where('name', $grp['category_name'])->first();
                $grp['category_id'] = $cat?->id ?? null;
            }
        }
        unset($grp);

        $maintenanceIdsStr = implode(',', $ids);
        $templateName = 'Inspeksi Area Terpadu';
        $maintenance = $maintenances->first();

        return view('technician.maintenance.inspect_unified', compact(
            'groupedTemplates', 'primaryTemplateId', 'assets', 'assetsByCategory',
            'primaryLocation', 'maintenanceIdsStr', 'templateName', 'maintenance'
        ));
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

            // Simpan Foto Bukti Global (Ditarik ke atas agar ID dan URL foto bisa dimasukkan ke semua Work Order)
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $photoPaths[] = $file->store('maintenance-evidence', 'public');
                }
            }

            $issuesByAsset = [];

            // Loop mengecek jawaban
            foreach ($request->answers as $itemId => $answer) {
                if ($answer === 'fail' || $answer === 'broken' || $answer === 'no') {
                    $hasIssue = true;

                    // --- FORMAT BARU: failed_assets[item_id][] = multiple asset_ids (Mass Triage) ---
                    $failedAssetIds = $request->input("failed_assets.{$itemId}", []);
                    // Juga cek format lama: failed_asset_ids[item_id] = single value
                    $legacySingleAsset = $request->input("failed_asset_ids.{$itemId}");

                    // Cari pertanyaan untuk detail deskripsi
                    $questionText = 'Pengecekan SOP';
                    $item = \App\Models\ChecklistItem::find($itemId);
                    if ($item) $questionText = $item->question;

                    // Logic Teks Deskripsi
                    $specificNote = !empty($request->notes[$itemId]) ? $request->notes[$itemId] : 'Tidak ada keterangan spesifik';
                    $baseDesc = "- SOP: {$questionText}\n  Keterangan: {$specificNote}";

                    if (!empty($failedAssetIds) && is_array($failedAssetIds)) {
                        foreach ($failedAssetIds as $selectedAssetId) {
                            $issuesByAsset[$selectedAssetId][] = $baseDesc;
                        }
                    } else if ($legacySingleAsset) {
                        $issuesByAsset[$legacySingleAsset][] = $baseDesc;
                    } else {
                        $issuesByAsset['area_general'][] = $baseDesc;
                    }
                }
            }

            if ($hasIssue && !empty($issuesByAsset)) {
                foreach ($issuesByAsset as $aId => $descLines) {
                    $selectedAssetId = ($aId === 'area_general') ? null : $aId;
                    
                    $finalDesc = count($descLines) > 1 
                        ? "Ditemukan " . count($descLines) . " masalah pada unit ini:\n" . implode("\n", $descLines)
                        : implode("\n", $descLines);

                    if (!empty($request->global_notes)) {
                         $finalDesc .= "\n\nCatatan Global Laporan: " . $request->global_notes;
                    }

                    $workOrder = \App\Models\WorkOrder::create([
                        'ticket_number'     => 'WO-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                        'asset_id'          => $selectedAssetId,
                        'location_id'       => $request->location_id,
                        'technician_id'     => null,
                        'reporter_id'       => Auth::id(),
                        'priority'          => 'medium',
                        'status'            => 'open',
                        'source'            => 'patrol',
                        'issue_description' => $finalDesc,
                        'maintenance_id'    => current($ids) ?: null,
                        'initial_photo'     => $photoPaths[0] ?? null,
                    ]);
                    $workOrdersCreated[] = $workOrder->id;
                }
            }

            // Buat 1 Log Patroli
            $patrolLog = \App\Models\PatrolLog::create([
                'technician_id'       => Auth::id(),
                'asset_id'            => null,
                'location_id'         => $request->location_id,
                'checklist_template_id' => $request->primary_template_id,
                'work_order_id'       => $workOrdersCreated[0] ?? null,
                'inspection_data'     => json_encode([
                    'answers'       => $request->answers,
                    'notes'         => $request->notes,
                    'failed_assets' => $request->failed_assets ?? [],   // format baru (multi)
                    'failed_asset_ids' => $request->failed_asset_ids ?? [], // format lama
                ]),
                'status'   => $hasIssue ? 'issue_found' : 'normal',
                'notes'    => $request->global_notes,
                'photos'   => $photoPaths,
                'shift_id' => Auth::user()->shift_id,
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