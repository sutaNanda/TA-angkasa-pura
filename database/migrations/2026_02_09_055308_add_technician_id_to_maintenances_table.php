<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Menambahkan kolom technician_id setelah id
            $table->foreignId('technician_id')
                  ->nullable() // Boleh kosong jika belum di-assign
                  ->after('id')
                  ->constrained('users') // Relasi ke tabel users
                  ->onDelete('set null'); // Jika user dihapus, data maintenance tetap ada (technician jadi null)
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });
    }
};
