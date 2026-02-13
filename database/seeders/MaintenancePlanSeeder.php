<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenancePlan;
use App\Models\Category;
use App\Models\ChecklistTemplate;
use Carbon\Carbon;

class MaintenancePlanSeeder extends Seeder
{
    public function run(): void
    {
        // Get categories and templates
        $networkCategory = Category::where('name', 'like', '%Network%')->first();
        $acCategory = Category::where('name', 'like', '%AC%')->orWhere('name', 'like', '%Air%')->first();
        $upsCategory = Category::where('name', 'like', '%UPS%')->first();
        
        $dailyNetworkTemplate = ChecklistTemplate::where('name', 'like', '%Network%')
            ->where('frequency', 'daily')->first();
        $weeklyAcTemplate = ChecklistTemplate::where('name', 'like', '%AC%')
            ->where('frequency', 'weekly')->first();
        $monthlyUpsTemplate = ChecklistTemplate::where('name', 'like', '%UPS%')
            ->where('frequency', 'monthly')->first();
        
        // Create plans
        $plans = [];
        
        if ($networkCategory && $dailyNetworkTemplate) {
            $plans[] = [
                'category_id' => $networkCategory->id,
                'checklist_template_id' => $dailyNetworkTemplate->id,
                'frequency' => 'daily',
                'start_date' => Carbon::today(),
                'is_active' => true,
                'notes' => 'Daily network equipment check',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if ($acCategory && $weeklyAcTemplate) {
            $plans[] = [
                'category_id' => $acCategory->id,
                'checklist_template_id' => $weeklyAcTemplate->id,
                'frequency' => 'weekly',
                'start_date' => Carbon::today()->startOfWeek(), // Monday
                'is_active' => true,
                'notes' => 'Weekly AC maintenance (every Monday)',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if ($upsCategory && $monthlyUpsTemplate) {
            $plans[] = [
                'category_id' => $upsCategory->id,
                'checklist_template_id' => $monthlyUpsTemplate->id,
                'frequency' => 'monthly',
                'start_date' => Carbon::today()->startOfMonth(), // 1st of month
                'is_active' => true,
                'notes' => 'Monthly UPS inspection (every 1st)',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($plans)) {
            MaintenancePlan::insert($plans);
            $this->command->info('✅ Maintenance plans seeded successfully!');
        } else {
            $this->command->warn('⚠️  No categories/templates found. Please seed categories and checklist templates first.');
        }
    }
}
