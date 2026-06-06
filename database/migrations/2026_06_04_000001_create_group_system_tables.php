<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Fase 1 — Buat tabel-tabel BARU untuk sistem grup.
 * Tidak menyentuh tabel lama sama sekali agar aman di-rollback.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel master grup teknisi (jika belum ada — sudah dibuat partial sebelumnya)
        if (!Schema::hasTable('technician_groups')) {
            Schema::create('technician_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');                          // Contoh: "Tim Mekanikal Pagi"
                $table->text('description')->nullable();
                $table->string('color', 30)->default('blue');   // Untuk badge UI (blue, green, red, dll)
                $table->timestamps();
            });
        }

        // 2. Pivot: Maintenance Plan ↔ Technician Group
        if (!Schema::hasTable('maintenance_plan_group')) {
            Schema::create('maintenance_plan_group', function (Blueprint $table) {
                $table->foreignId('maintenance_plan_id')
                      ->constrained('maintenance_plans')
                      ->cascadeOnDelete();

                $table->foreignId('technician_group_id')
                      ->constrained('technician_groups')
                      ->cascadeOnDelete();

                $table->time('start_time')->nullable();
                $table->timestamps();
                $table->primary(['maintenance_plan_id', 'technician_group_id']);
            });
        }

        // 3. Audit trail setiap handover tiket antar-grup
        if (!Schema::hasTable('work_order_handovers')) {
            Schema::create('work_order_handovers', function (Blueprint $table) {
                $table->id();

                $table->foreignId('work_order_id')
                      ->constrained('work_orders')
                      ->cascadeOnDelete();

                // Grup pengirim (boleh null jika handover dari pool umum)
                $table->foreignId('from_group_id')
                      ->nullable()
                      ->constrained('technician_groups')
                      ->nullOnDelete();

                // Grup penerima — cascade delete jika grup dihapus
                $table->foreignId('to_group_id')
                      ->constrained('technician_groups')
                      ->cascadeOnDelete();

                // Nullable agar handover tetap tersimpan walau teknisinya dihapus
                $table->foreignId('handed_over_by_user_id')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();

                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Hapus dalam urutan terbalik untuk menghindari FK constraint error
        Schema::dropIfExists('work_order_handovers');
        Schema::dropIfExists('maintenance_plan_group');
        Schema::dropIfExists('technician_groups');
    }
};
