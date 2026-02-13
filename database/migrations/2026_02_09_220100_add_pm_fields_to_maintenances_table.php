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
            $table->foreignId('maintenance_schedule_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->date('scheduled_date')->nullable()->after('maintenance_schedule_id');
            $table->enum('type', ['preventive', 'corrective'])->default('preventive')->after('scheduled_date');
            
            // Index for querying today's tasks
            $table->index(['scheduled_date', 'status']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['maintenance_schedule_id']);
            $table->dropColumn(['maintenance_schedule_id', 'scheduled_date', 'type']);
        });
    }
};
