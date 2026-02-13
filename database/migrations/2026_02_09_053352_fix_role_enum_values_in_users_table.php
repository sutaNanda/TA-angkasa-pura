<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // LANGKAH 1: Ubah kolom 'role' jadi VARCHAR dulu agar tidak error "Data Truncated"
        // Ini memberi kita fleksibilitas untuk memperbaiki data sebelum dikunci lagi jadi ENUM.
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user'");

        // LANGKAH 2: Normalisasi Data (PENTING)
        // Jika sebelumnya ada data 'technician' (bahasa inggris), kita ubah jadi 'teknisi' (indo)
        // agar sesuai dengan ENUM baru.
        DB::table('users')->where('role', 'technician')->update(['role' => 'teknisi']);

        // Pastikan tidak ada role kosong/salah, paksa jadi 'user' jika tidak dikenali
        DB::table('users')
            ->whereNotIn('role', ['admin', 'teknisi', 'manajer', 'user'])
            ->update(['role' => 'user']);

        // LANGKAH 3: Ubah kembali ke ENUM dengan daftar final yang diinginkan
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teknisi', 'manajer', 'user') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika di-rollback, kembalikan ke VARCHAR saja agar data aman
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user'");
    }
};
