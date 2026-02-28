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
            ->with(['category', 'checklistTemplate', 'assets'])
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
            
            $category = $plan->category;
            $template = $plan->checklistTemplate;
            
            if ($plan->assets->isNotEmpty()) {
                // Get ONLY specific assets that DON'T have this task today
                $assetIds = $plan->assets->pluck('id')->toArray();
                $assets = Asset::whereIn('id', $assetIds)
                    ->whereDoesntHave('maintenances', function($q) use ($today, $plan) {
                        $q->where('checklist_template_id', $plan->checklist_template_id)
                          ->whereDate('scheduled_date', $today);
                    })
                    ->get();
            } else {
                // Get ALL assets in this category that DON'T have this task today
                $assets = Asset::where('category_id', $plan->category_id)
                    ->whereDoesntHave('maintenances', function($q) use ($today, $plan) {
                        $q->where('checklist_template_id', $plan->checklist_template_id)
                          ->whereDate('scheduled_date', $today);
                    })
                    ->get();
            }
            
            if ($assets->isEmpty()) {
                $totalSkipped++;
                continue;
            }
            
            $this->line("  ✓ {$category->name} - {$template->name} ({$plan->frequency})");
            $this->line("    → {$assets->count()} assets to process");
            
            if (!$isDryRun) {
                // Prepare bulk insert data
                $tasks = [];
                foreach ($assets as $asset) {
                    $tasks[] = [
                        'asset_id' => $asset->id,
                        'maintenance_plan_id' => $plan->id,
                        'checklist_template_id' => $plan->checklist_template_id,
                        'scheduled_date' => $today,
                        'type' => 'preventive',
                        'status' => 'pending', // Fixed: use 'pending' not 'OPEN'
                        'technician_id' => null, // Goes to pool
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                // Bulk insert for performance
                try {
                    Maintenance::insert($tasks);
                    $totalCreated += count($tasks);
                } catch (\Exception $e) {
                    $this->error("    ✗ Error creating tasks: " . $e->getMessage());
                    continue;
                }
            } else {
                $totalCreated += $assets->count();
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
