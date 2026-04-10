<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PatrolLog;
use App\Models\Maintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurgeOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:purge-logs {months=3 : The number of months to retain logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old maintenance tasks and patrol logs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = (int) $this->argument('months');
        $cutoffDate = Carbon::now()->subMonths($months);

        $this->info("Starting cleanup for records older than {$months} months (Before {$cutoffDate->toDateString()})...");

        DB::beginTransaction();

        try {
            // 1. Ambil ID Maintenance yang akan dihapus (Hanya yang statusnya 'completed' atau 'cancelled' untuk keamanan)
            $maintenanceIds = Maintenance::where('scheduled_date', '<', $cutoffDate)
                ->whereIn('status', ['completed', 'cancelled'])
                ->pluck('id');

            $countMaintenance = $maintenanceIds->count();

            if ($countMaintenance > 0) {
                // 2. Hapus PatrolLog yang terkait dengan Maintenance tersebut
                $countPatrol = PatrolLog::whereIn('id', function($query) use ($maintenanceIds) {
                    $query->select('id')->from('patrol_logs')->whereIn('id', $maintenanceIds); // Assuming 1:1 or logic exists
                })->orWhere('created_at', '<', $cutoffDate)->delete();

                // 3. Hapus Maintenance records
                Maintenance::whereIn('id', $maintenanceIds)->delete();

                DB::commit();
                $this->success("Successfully purged {$countMaintenance} maintenance records and related logs.");
            } else {
                // Jika tidak ada maintenance, coba hapus PatrolLog independen (jika ada)
                $countPatrol = PatrolLog::where('created_at', '<', $cutoffDate)->delete();
                DB::commit();
                $this->info("No old maintenance tasks found. Purged {$countPatrol} orphaned patrol logs.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during purge: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
