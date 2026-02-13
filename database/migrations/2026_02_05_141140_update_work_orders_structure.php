<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Kolom Baru (Jika belum ada)
        Schema::table('work_orders', function (Blueprint $table) {
            // Kolom untuk mencatat siapa yang membuat tiket (Admin/Sistem)
            if (!Schema::hasColumn('work_orders', 'reported_by')) {
                $table->foreignId('reported_by')->nullable()->after('technician_id')->constrained('users');
            }

            // Kolom untuk mencatat kapan tiket selesai dikerjakan teknisi
            if (!Schema::hasColumn('work_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('maintenance_id');
            }
        });

        // 2. Update ENUM Status (Pakai Raw SQL agar aman untuk data lama)
        // Kita perluas opsi statusnya
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM('open', 'in_progress', 'pending_part', 'handover', 'completed', 'verified') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        // Rollback (Opsional, kembalikan ke status awal)
        // DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM('open', 'in_progress', 'completed', 'verified') NOT NULL DEFAULT 'open'");
    }
};