<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaintenancePlan;
use App\Models\Asset;
use App\Models\Maintenance;
use Carbon\Carbon;

class GenerateDailyMaintenanceTasks extends Command
{
    protected $signature   = 'maintenance:generate-daily {--dry-run}';
    protected $description = 'Generate daily maintenance tasks based on group-based maintenance plans';

    public function handle(): int
    {
        $isDryRun = false;
        try {
            if (isset($this->input) && $this->input->hasOption('dry-run')) {
                $isDryRun = $this->option('dry-run');
            }
        } catch (\Exception) {}

        $today = now()->startOfDay();

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE — No tasks will be created');
        }

        $this->info("📅 Generating maintenance tasks for: {$today->toDateString()}");
        $this->newLine();

        // =====================================================================
        // Eager-load relasi 'groups' untuk mencegah N+1 Query.
        // Pivot 'start_time' diakses via $group->pivot->start_time.
        // =====================================================================
        $plans = MaintenancePlan::where('is_active', true)
            ->with(['assets', 'locations', 'groups']) // N+1 prevention
            ->get();

        if ($plans->isEmpty()) {
            $this->warn('⚠️  No active maintenance plans found.');
            return Command::SUCCESS;
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($plans as $plan) {
            // Cek apakah rencana ini harus berjalan hari ini berdasarkan frekuensi
            if (!$plan->shouldRunToday($today)) {
                continue;
            }

            if (empty($plan->template_configs)) {
                $this->warn("  ⚠️  Plan '{$plan->name}' has no template configurations.");
                continue;
            }

            // Kumpulkan category_id yang dikonfigurasi di plan ini
            $configCategoryIds = collect($plan->template_configs)->pluck('category_id')->toArray();

            // Tentukan aset target berdasarkan target_type plan
            $targetAssetIds = $this->resolveTargetAssetIds($plan, $configCategoryIds);

            if (empty($targetAssetIds)) {
                $totalSkipped++;
                $this->line("  ⏭  Plan '{$plan->name}' skipped — no matching assets.");
                continue;
            }

            // =====================================================================
            // LOOP PER GRUP
            // Setiap plan bisa memiliki banyak grup dengan start_time berbeda.
            // Satu record Maintenance di-generate per-grup per-lokasi.
            // =====================================================================
            if ($plan->groups->isEmpty()) {
                // Fallback: Jika plan tidak punya grup, buat satu tugas tanpa group
                $this->processPlanForGroup($plan, null, null, $targetAssetIds, $today, $isDryRun, $totalCreated, $totalSkipped);
            } else {
                foreach ($plan->groups as $group) {
                    // Ambil start_time dari kolom pivot
                    $startTime = $group->pivot->start_time;

                    $this->processPlanForGroup($plan, $group->id, $startTime, $targetAssetIds, $today, $isDryRun, $totalCreated, $totalSkipped);
                }
            }
        }

        $this->newLine();
        $this->info("✅ Generated: {$totalCreated} tasks");
        $this->info("⏭️  Skipped:   {$totalSkipped} plans/groups (no assets or already exist)");

        if ($isDryRun) {
            $this->warn('⚠️  This was a dry run. Run without --dry-run to actually create tasks.');
        }

        return Command::SUCCESS;
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    /**
     * Tentukan ID aset yang harus dicakup oleh plan ini.
     */
    private function resolveTargetAssetIds(MaintenancePlan $plan, array $configCategoryIds): array
    {
        if ($plan->target_type === 'location' && $plan->locations->isNotEmpty()) {
            $locationIds = $plan->locations->pluck('id')->toArray();

            $physical = Asset::whereIn('location_id', $locationIds)
                ->whereIn('category_id', $configCategoryIds)
                ->pluck('id')->toArray();

            $software = Asset::whereHas('parentAsset', fn($q) => $q->whereIn('location_id', $locationIds))
                ->whereIn('category_id', $configCategoryIds)
                ->pluck('id')->toArray();

            return array_unique(array_merge($physical, $software));
        }

        if ($plan->target_type === 'asset' && $plan->assets->isNotEmpty()) {
            return $plan->assets
                ->whereIn('category_id', $configCategoryIds)
                ->pluck('id')->toArray();
        }

        // Fallback: semua aset dalam kategori yang dikonfigurasi
        return Asset::whereIn('category_id', $configCategoryIds)->pluck('id')->toArray();
    }

    /**
     * Proses pembuatan tugas untuk satu kombinasi plan + grup.
     *
     * @param int|null    $groupId    ID grup (null = tugas tanpa grup)
     * @param string|null $startTime  Waktu mulai dari pivot (format 'HH:MM:SS')
     */
    private function processPlanForGroup(
        MaintenancePlan $plan,
        ?int            $groupId,
        ?string         $startTime,
        array           $targetAssetIds,
        Carbon          $today,
        bool            $isDryRun,
        int             &$totalCreated,
        int             &$totalSkipped
    ): void {
        // Cek apakah tugas untuk kombinasi plan + grup + waktu ini sudah ada hari ini
        // Mencegah duplikat jika command dijalankan lebih dari sekali sehari
        $existingQuery = Maintenance::whereDate('scheduled_date', $today)
            ->where('maintenance_plan_id', $plan->id)
            ->where('technician_group_id', $groupId); // null == null di SQL dengan whereNull

        if ($startTime) {
            $existingQuery->where('scheduled_time', $startTime);
        } else {
            $existingQuery->whereNull('scheduled_time');
        }

        $coveredAssetIds = $existingQuery->get()
            ->flatMap(fn($m) => is_array($m->target_asset_ids) ? $m->target_asset_ids : [$m->asset_id])
            ->filter()
            ->unique()
            ->toArray();

        // Aset yang belum tercakup (belum ada tugasnya hari ini)
        $remainingAssetIds = array_diff($targetAssetIds, $coveredAssetIds);

        if (empty($remainingAssetIds)) {
            $totalSkipped++;
            return;
        }

        $assets = Asset::with(['parentAsset', 'category'])
            ->whereIn('id', $remainingAssetIds)
            ->get();

        $timeLabel   = $startTime ?? 'Fleksibel';
        $groupLabel  = $groupId ? "Grup #{$groupId}" : 'Tanpa Grup';
        $categoryNames = collect($plan->template_configs)
            ->map(fn($c) => \App\Models\Category::find($c['category_id'])?->name ?? '?')
            ->implode(', ');

        $this->line("  ✓ {$plan->name} [{$categoryNames}] @ {$timeLabel} [{$groupLabel}]");
        $this->line("    → {$assets->count()} new assets to process");

        if ($isDryRun) {
            $totalCreated += $assets->groupBy('location_id')->count();
            return;
        }

        // Group aset berdasarkan lokasi untuk membuat 1 record Maintenance per lokasi per grup
        $groupedAssets = $assets->groupBy(function (Asset $asset) {
            if ($asset->location_id) {
                return $asset->location_id;
            }
            if ($asset->parentAsset?->location_id) {
                return $asset->parentAsset->location_id;
            }
            return 'virtual';
        });

        foreach ($groupedAssets as $locationId => $assetsInLocation) {
            try {
                $dbLocationId = $locationId === 'virtual' ? null : $locationId;

                Maintenance::create([
                    'maintenance_plan_id'  => $plan->id,
                    'checklist_template_id' => null,         // Multi-template diatur via plan
                    'location_id'          => $dbLocationId,
                    'target_asset_ids'     => $assetsInLocation->pluck('id')->toArray(),
                    'scheduled_date'       => $today,
                    'scheduled_time'       => $startTime,   // Jam mulai dari pivot grup
                    'type'                 => 'preventive',
                    'status'               => 'pending',
                    'technician_id'        => null,
                    // Tandai grup yang bertanggung jawab atas tugas ini
                    'technician_group_id'  => $groupId,
                ]);

                $totalCreated++;
            } catch (\Exception $e) {
                $this->error("    ✗ Error creating task: " . $e->getMessage());
            }
        }
    }
}
