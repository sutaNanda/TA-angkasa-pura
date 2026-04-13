<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaintenancePlan;
use App\Models\Asset;
use App\Models\Maintenance;
use Carbon\Carbon;

class GenerateDailyMaintenanceTasks extends Command
{
    protected $signature = 'maintenance:generate-daily {--dry-run}';
    protected $description = 'Generate daily maintenance tasks based on category plans';

    public function handle()
    {
        $isDryRun = false;
        try { if (isset($this->input) && $this->input->hasOption('dry-run')) $isDryRun = $this->option('dry-run'); } catch (\Exception $e) {}
        $today = now()->startOfDay();
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No tasks will be created');
        }
        
        $this->info("📅 Generating maintenance tasks for: {$today->toDateString()}");
        $this->newLine();
        
        $shifts = \App\Models\Shift::all();
        
        // Get all active maintenance plans
        $plans = MaintenancePlan::where('is_active', true)
            ->with(['assets', 'locations'])
            ->get();
        
        if ($plans->isEmpty()) {
            $this->warn('⚠️  No active maintenance plans found.');
            return Command::SUCCESS;
        }
        
        $totalCreated = 0;
        $totalSkipped = 0;
        
        foreach ($plans as $plan) {
            // Check if this plan should run today
            if (!$plan->shouldRunToday($today)) {
                continue;
            }

            if (empty($plan->template_configs)) {
                $this->warn("  ⚠️  Plan '{$plan->name}' has no template configurations.");
                continue;
            }
            
            $configCategoryIds = collect($plan->template_configs)->pluck('category_id')->toArray();
            
            // 1. Get all assets that SHOULD be covered by this plan
            $targetAssetIds = [];
            
            if ($plan->target_type === 'location' && $plan->locations->isNotEmpty()) {
                $locationIds = $plan->locations->pluck('id')->toArray();
                
                $physicalAssets = Asset::whereIn('location_id', $locationIds)
                    ->whereIn('category_id', $configCategoryIds)
                    ->pluck('id')->toArray();
                    
                $softwareAssets = Asset::whereHas('parentAsset', function($q) use ($locationIds) {
                        $q->whereIn('location_id', $locationIds);
                    })
                    ->whereIn('category_id', $configCategoryIds)
                    ->pluck('id')->toArray();
                    
                $targetAssetIds = array_unique(array_merge($physicalAssets, $softwareAssets));
                
            } elseif ($plan->target_type === 'asset' && $plan->assets->isNotEmpty()) {
                $targetAssetIds = $plan->assets
                    ->whereIn('category_id', $configCategoryIds)
                    ->pluck('id')
                    ->toArray();
            } else {
                if ($plan->assets->isNotEmpty()) {
                    $targetAssetIds = $plan->assets->whereIn('category_id', $configCategoryIds)->pluck('id')->toArray();
                } else {
                    $targetAssetIds = Asset::whereIn('category_id', $configCategoryIds)->pluck('id')->toArray();
                }
            }

            if (empty($targetAssetIds)) {
                $totalSkipped++;
                continue;
            }
            
            $targetTimes = [];
            if ($plan->shift_id) {
                // Specific shift: use plan's start_time OR fallback to the shift's own start_time
                $shift = $shifts->firstWhere('id', $plan->shift_id);
                $targetTimes[] = $plan->start_time ?? ($shift->start_time ?? null);
            } else {
                // "Semua Shift (Berulang Tiap Shift)": Generate once per shift, using each shift's start_time
                foreach ($shifts as $shift) {
                    $targetTimes[] = $shift->start_time;
                }
            }

            foreach ($targetTimes as $targetTime) {
                // 2. Identify assets that are ALREADY covered today for this PLAN AT THIS SPECIFIC TIME
                $query = Maintenance::whereDate('scheduled_date', $today)
                    ->where('maintenance_plan_id', $plan->id);
                
                if ($targetTime) {
                    $query->where('scheduled_time', $targetTime);
                } else {
                    $query->whereNull('scheduled_time');
                }

                $coveredAssetIds = $query->get()
                    ->flatMap(function ($m) {
                        return is_array($m->target_asset_ids) ? $m->target_asset_ids : [$m->asset_id];
                    })
                    ->filter()
                    ->unique()
                    ->toArray();

                // 3. Filter out covered assets
                $remainingAssetIds = array_diff($targetAssetIds, $coveredAssetIds);

                if (empty($remainingAssetIds)) {
                    $totalSkipped++;
                    continue; // Skip THIS shift's generation, not the whole plan
                }

                $assets = Asset::with(['parentAsset', 'category'])->whereIn('id', $remainingAssetIds)->get();
                
                $categoryNames = collect($plan->template_configs)
                    ->map(fn($c) => \App\Models\Category::find($c['category_id'])->name ?? '?')
                    ->implode(', ');

                $timeLabel = $targetTime ? $targetTime : 'Fleksibel';
                $this->line("  ✓ {$plan->name} [{$categoryNames}] @ {$timeLabel}");
                $this->line("    → {$assets->count()} new assets to process");
                
                if (!$isDryRun) {
                    // 4. GROUP BY Location ID (Resolving physical location for software)
                    $groupedAssets = $assets->groupBy(function ($asset) {
                        if ($asset->location_id) return $asset->location_id;
                        if ($asset->parentAsset && $asset->parentAsset->location_id) return $asset->parentAsset->location_id;
                        return 'virtual';
                    });

                    foreach ($groupedAssets as $locationId => $assetsInLocation) {
                        try {
                            $dbLocationId = $locationId === 'virtual' ? null : $locationId;
                            Maintenance::create([
                                'maintenance_plan_id' => $plan->id,
                                'checklist_template_id' => null, // Multiple templates handled by plan
                                'location_id' => $dbLocationId,
                                'target_asset_ids' => $assetsInLocation->pluck('id')->toArray(),
                                'scheduled_date' => $today,
                                'scheduled_time' => $targetTime, // Assign specific shift start time
                                'type' => 'preventive',
                                'status' => 'pending',
                                'technician_id' => null,
                            ]);
                            $totalCreated++;
                        } catch (\Exception $e) {
                            $this->error("    ✗ Error creating task for location " . ($locationId ?: 'null') . ": " . $e->getMessage());
                        }
                    }
                } else {
                    $totalCreated += $assets->groupBy('location_id')->count();
                }
            } // End of $targetTimes loop
        }
        
        $this->newLine();
        $this->info("✅ Generated: {$totalCreated} tasks");
        $this->info("⏭️  Skipped: {$totalSkipped} plans (no matching assets or already exist)");
        
        if ($isDryRun) {
            $this->warn('⚠️  This was a dry run. Run without --dry-run to actually create tasks.');
        }
        
        return Command::SUCCESS;
    }
}
