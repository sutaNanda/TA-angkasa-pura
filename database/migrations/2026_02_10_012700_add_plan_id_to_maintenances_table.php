<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Add maintenance_plan_id (nullable for backward compatibility)
            $table->foreignId('maintenance_plan_id')->nullable()->after('id')->constrained('maintenance_plans')->onDelete('set null');
            
            // Add unique constraint to prevent duplicate tasks
            $table->unique(['asset_id', 'checklist_template_id', 'scheduled_date'], 'unique_daily_maintenance_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['maintenance_plan_id']);
            $table->dropColumn('maintenance_plan_id');
            $table->dropUnique('unique_daily_maintenance_task');
        });
    }
};
