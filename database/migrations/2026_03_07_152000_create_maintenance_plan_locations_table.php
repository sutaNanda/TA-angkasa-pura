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
        // Add target_type to maintenance_plans table
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->enum('target_type', ['asset', 'location'])->default('asset')->after('name');
        });

        // Create pivot table for maintenance_plan_locations
        Schema::create('maintenance_plan_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Prevent duplicate entries
            $table->unique(['maintenance_plan_id', 'location_id'], 'maint_plan_loc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_plan_locations');
        
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropColumn('target_type');
        });
    }
};
