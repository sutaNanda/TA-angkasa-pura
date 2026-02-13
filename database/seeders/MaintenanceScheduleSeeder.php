<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\ChecklistTemplate;

class MaintenanceScheduleSeeder extends Seeder
{
    /**
     * Seed sample maintenance schedules
     */
    public function run(): void
    {
        // Get sample assets and templates
        $assets = Asset::with('category')->take(10)->get();
        
        foreach ($assets as $asset) {
            // Get checklist templates for this asset's category
            $templates = ChecklistTemplate::where('category_id', $asset->category_id)->get();
            
            foreach ($templates as $template) {
                // Create schedule based on template frequency
                $scheduleData = [
                    'asset_id' => $asset->id,
                    'checklist_template_id' => $template->id,
                    'frequency' => $template->frequency,
                    'is_active' => true,
                ];
                
                // Set day_of_week or day_of_month based on frequency
                if ($template->frequency === 'weekly') {
                    $scheduleData['day_of_week'] = 1; // Monday
                } elseif ($template->frequency === 'monthly') {
                    $scheduleData['day_of_month'] = 1; // 1st of month
                }
                
                MaintenanceSchedule::create($scheduleData);
            }
        }
        
        $this->command->info('✅ Maintenance schedules seeded successfully!');
    }
}
