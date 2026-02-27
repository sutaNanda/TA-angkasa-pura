<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel maintenance_schedules (sistem PM lama).
     * Digantikan sepenuhnya oleh maintenance_plans (rule-based, lebih fleksibel).
     */
    public function up(): void
    {
        // ─── Step 1: Lepas FK di tabel maintenances yang mengarah ke maintenance_schedules
        // Harus dilakukan DULU sebelum drop tabel induknya agar tidak FK constraint error
        Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'maintenance_schedule_id')) {
                $table->dropForeign(['maintenance_schedule_id']);
                $table->dropColumn('maintenance_schedule_id');
            }
        });

        // ─── Step 2: Baru aman drop tabel maintenance_schedules
        Schema::dropIfExists('maintenance_schedules');
    }

    /**
     * Rollback: buat ulang tabel (tanpa data).
     */
    public function down(): void
    {
        Schema::create('maintenance_schedules', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_template_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->time('preferred_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
