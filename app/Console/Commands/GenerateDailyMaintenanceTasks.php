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
        $isDryRun = $this->option('dry-run');
        $today = now()->startOfDay();
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No tasks will be created');
        }
        
        $this->info("📅 Generating maintenance tasks for: {$today->toDateString()}");
        $this->newLine();
        
        // Get all active maintenance plans
        $plans = MaintenancePlan::where('is_active', true)
            ->with(['assets'])
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
            if ($plan->assets->isNotEmpty()) {
                // Filter plan specific assets to ensure they belong to requested categories
                $targetAssetIds = $plan->assets
                    ->whereIn('category_id', $configCategoryIds)
                    ->pluck('id')
                    ->toArray();
            } else {
                // Get all assets in the specified categories
                $targetAssetIds = Asset::whereIn('category_id', $configCategoryIds)->pluck('id')->toArray();
            }

            if (empty($targetAssetIds)) {
                $totalSkipped++;
                continue;
            }

            // 2. Identify assets that are ALREADY covered today for this PLAN
            // Since a plan now has multiple templates, we check if ANY maintenance 
            // from this plan already covers the asset today.
            $coveredAssetIds = Maintenance::whereDate('scheduled_date', $today)
                ->where('maintenance_plan_id', $plan->id)
                ->get()
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
                continue;
            }

            $assets = Asset::whereIn('id', $remainingAssetIds)->get();
            
            $categoryNames = collect($plan->template_configs)
                ->map(fn($c) => \App\Models\Category::find($c['category_id'])->name ?? '?')
                ->implode(', ');

            $this->line("  ✓ {$plan->name} [{$categoryNames}]");
            $this->line("    → {$assets->count()} new assets to process");
            
            if (!$isDryRun) {
                // 4. GROUP BY Location ID
                $groupedAssets = $assets->groupBy('location_id');

                foreach ($groupedAssets as $locationId => $assetsInLocation) {
                    try {
                        Maintenance::create([
                            'maintenance_plan_id' => $plan->id,
                            'checklist_template_id' => null, // Multiple templates handled by plan
                            'location_id' => $locationId ?: null,
                            'target_asset_ids' => $assetsInLocation->pluck('id')->toArray(),
                            'scheduled_date' => $today,
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
