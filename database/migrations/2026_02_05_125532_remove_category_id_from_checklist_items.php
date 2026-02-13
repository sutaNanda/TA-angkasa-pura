<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            // Hapus kolom category_id karena sudah tidak dipakai di level item
            // (Sudah pindah ke level Template)
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            // Kembalikan kolom jika di-rollback (nullable agar aman)
            $table->foreignId('category_id')->nullable()->constrained('categories');
        });
    }
};
