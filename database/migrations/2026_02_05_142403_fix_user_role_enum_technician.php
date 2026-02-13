<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // TAHAP 1: Ubah jadi VARCHAR(255) dan NULLABLE
        // Ini membuat kolom menerima teks apa saja, jadi tidak mungkin error "Truncated"
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NULL DEFAULT 'user'");

        // TAHAP 2: Sanitasi Data (PENTING)
        // Cari data yang BUKAN 'admin', 'user', atau 'technician', lalu paksa jadi 'user'
        // Ini memastikan saat diubah ke ENUM nanti, semua datanya valid
        DB::table('users')
            ->whereNotIn('role', ['admin', 'user', 'technician'])
            ->update(['role' => 'user']);
        
        // Juga handle yang NULL (jika ada)
        DB::table('users')
            ->whereNull('role')
            ->update(['role' => 'user']);

        // TAHAP 3: Ubah ke ENUM Target
        // Karena data sudah bersih di Tahap 2, tahap ini pasti sukses
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'technician') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        // Opsional: Rollback
    }
};