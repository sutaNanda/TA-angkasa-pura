<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom parent_asset_id (self-referencing FK)
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('parent_asset_id')
                  ->nullable()
                  ->after('location_id')
                  ->constrained('assets')
                  ->nullOnDelete();
        });

        // 2. Expand ENUM status untuk mendukung Software/Lisensi
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('normal', 'rusak', 'maintenance', 'hilang', 'aktif', 'kedaluwarsa', 'ditangguhkan') NOT NULL DEFAULT 'normal'");
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['parent_asset_id']);
            $table->dropColumn('parent_asset_id');
        });

        // Revert ENUM (hanya jika tidak ada data dengan status baru)
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('normal', 'rusak', 'maintenance', 'hilang') NOT NULL DEFAULT 'normal'");
    }
};
