<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // LANGKAH 1: Buka Kunci Tipe Data
        // Ubah dulu jadi VARCHAR(100) agar menerima teks apapun. 
        // Ini solusi untuk error "Data truncated".
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(100) NOT NULL DEFAULT 'user'");

        // LANGKAH 2: Bersihkan Data Sampah
        // Jika ada role aneh (bukan admin/user), paksa ubah jadi 'user'
        DB::table('users')
            ->whereNotIn('role', ['admin', 'user'])
            ->update(['role' => 'user']);

        // LANGKAH 3: Kunci Kembali menjadi ENUM Baru
        // Sekarang data sudah bersih, jadi aman untuk diubah ke ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'technician') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        // Opsional
    }
};