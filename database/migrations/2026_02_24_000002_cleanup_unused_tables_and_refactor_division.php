<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tujuan migration ini:
     * 1. Hapus tabel maintenance_details (tidak digunakan dalam logika bisnis).
     * 2. Hapus foreign key division_id dari tabel users.
     * 3. Tambah kolom division (string) sebagai pengganti yang ringan (tanpa relasi).
     * 4. Hapus tabel divisions.
     */
    public function up(): void
    {
        // ─── 1. Drop tabel maintenance_details ──────────────────────────────────
        Schema::dropIfExists('maintenance_details');

        // ─── 2. Migrasi users: ganti division_id (FK) → division (string) ───────
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            if (Schema::hasColumn('users', 'division_id')) {
                $table->dropForeign(['division_id']);
                $table->dropColumn('division_id');
            }

            // Tambah kolom baru: string sederhana, nullable, tanpa FK
            // Ditempatkan setelah kolom 'role'
            if (!Schema::hasColumn('users', 'division')) {
                $table->string('division')->nullable()->after('role');
            }
        });

        // ─── 3. Drop tabel divisions (sudah tidak punya FK yang bergantung) ─────
        Schema::dropIfExists('divisions');
    }

    /**
     * Reverse: kembalikan ke struktur semula (untuk rollback).
     */
    public function down(): void
    {
        // Buat ulang tabel divisions
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Kembalikan division_id ke users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'division')) {
                $table->dropColumn('division');
            }
            $table->foreignId('division_id')->nullable()->after('role')->constrained('divisions')->nullOnDelete();
        });

        // Buat ulang tabel maintenance_details (struktur asli)
        Schema::create('maintenance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained()->cascadeOnDelete();
            $table->string('item');
            $table->enum('status', ['ok', 'not_ok', 'na'])->default('na');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
