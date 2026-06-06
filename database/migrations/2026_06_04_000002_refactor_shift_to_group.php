<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migrasi Fase 2 — Refactor tabel LAMA: hapus kolom shift, tambah kolom grup.
 *
 * PERINGATAN: Migrasi ini bersifat DESTRUCTIVE.
 * Pastikan backup database sebelum dijalankan di production.
 *
 * Urutan eksekusi penting untuk menghindari FK constraint error:
 * 1. Alter tabel yang tidak memiliki dependensi ke grup dulu (users, maintenance_plans)
 * 2. Baru alter tabel yang FK-nya bergantung (maintenances, patrol_logs, work_orders)
 */
return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // 1. TABEL USERS
        // Hapus shift_id (One-to-One ke Shift), ganti dengan technician_group_id
        // (One-to-Many dari TechnicianGroup ke User).
        // =====================================================================
        Schema::table('users', function (Blueprint $table) {
            // Drop FK & kolom lama
            if (Schema::hasColumn('users', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }

            // Tambah FK baru ke grup (nullable: Admin & Manajer tidak memiliki grup)
            $table->foreignId('technician_group_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('technician_groups')
                  ->nullOnDelete();
        });

        // =====================================================================
        // 2. TABEL MAINTENANCE_PLANS
        // Hapus shift_id dan start_time (start_time dipindah ke pivot maintenance_plan_group).
        // =====================================================================
        Schema::table('maintenance_plans', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_plans', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }

            // start_time sekarang disimpan di pivot per-grup, bukan di plan
            if (Schema::hasColumn('maintenance_plans', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });

        // =====================================================================
        // 3. TABEL MAINTENANCES (Tugas Harian)
        // Tambah technician_group_id untuk menandai grup mana yang dijadwalkan
        // mengerjakan tugas ini pada hari tersebut.
        // =====================================================================
        Schema::table('maintenances', function (Blueprint $table) {
            // Drop shift_id jika ada (dari migrasi sebelumnya)
            if (Schema::hasColumn('maintenances', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }

            $table->foreignId('technician_group_id')
                  ->nullable()
                  ->after('technician_id')
                  ->constrained('technician_groups')
                  ->nullOnDelete();
        });

        // =====================================================================
        // 4. TABEL PATROL_LOGS
        // Ganti shift_id dengan technician_group_id untuk mencatat siapa
        // (grup mana) yang melakukan inspeksi.
        // =====================================================================
        Schema::table('patrol_logs', function (Blueprint $table) {
            if (Schema::hasColumn('patrol_logs', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }

            $table->foreignId('technician_group_id')
                  ->nullable()
                  ->constrained('technician_groups')
                  ->nullOnDelete();
        });

        // =====================================================================
        // 5. TABEL WORK_ORDERS
        // - assigned_group_id: Grup yang ditugaskan (nullable = Pool Umum)
        // - executed_by_user_id: Teknisi individu yang secara aktif mengerjakan
        // - Tambah ENUM 'handed_over' untuk status handover antar-grup
        // =====================================================================
        Schema::table('work_orders', function (Blueprint $table) {
            // Grup yang di-assign tiket ini (null = Pool Umum, siapapun bisa klaim)
            $table->foreignId('assigned_group_id')
                  ->nullable()
                  ->after('technician_id')
                  ->constrained('technician_groups')
                  ->nullOnDelete();

            // Teknisi spesifik yang sedang mengerjakan (diisi saat claim/start)
            $table->foreignId('executed_by_user_id')
                  ->nullable()
                  ->after('assigned_group_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        // Tambah value 'handed_over' ke ENUM status menggunakan raw SQL
        // Gunakan MODIFY COLUMN agar tetap backward-compatible dengan value lama
        DB::statement("
            ALTER TABLE work_orders
            MODIFY COLUMN status
            ENUM('open','in_progress','pending_part','handover','handed_over','completed','verified')
            NOT NULL DEFAULT 'open'
        ");
    }

    public function down(): void
    {
        // =====================================================================
        // Rollback: kembalikan semua perubahan dalam urutan terbalik
        // =====================================================================

        // Rollback work_orders
        DB::statement("
            ALTER TABLE work_orders
            MODIFY COLUMN status
            ENUM('open','in_progress','pending_part','handover','completed','verified')
            NOT NULL DEFAULT 'open'
        ");
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['executed_by_user_id']);
            $table->dropForeign(['assigned_group_id']);
            $table->dropColumn(['executed_by_user_id', 'assigned_group_id']);
        });

        // Rollback patrol_logs
        Schema::table('patrol_logs', function (Blueprint $table) {
            $table->dropForeign(['technician_group_id']);
            $table->dropColumn('technician_group_id');
        });

        // Rollback maintenances
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['technician_group_id']);
            $table->dropColumn('technician_group_id');
        });

        // Rollback maintenance_plans (kembalikan start_time & shift_id)
        // Catatan: Data shift_id lama tidak bisa dipulihkan otomatis
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date');
        });

        // Rollback users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['technician_group_id']);
            $table->dropColumn('technician_group_id');
        });
    }
};
