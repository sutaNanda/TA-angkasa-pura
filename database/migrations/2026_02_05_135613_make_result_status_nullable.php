<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kita gunakan Raw SQL agar aman untuk mengubah kolom ENUM di MySQL
        // Perintah ini mengubah kolom result_status menjadi BOLEH NULL
        DB::statement("ALTER TABLE maintenances MODIFY COLUMN result_status ENUM('pass', 'fail') NULL");
    }

    public function down(): void
    {
        // Kembalikan ke tidak boleh null (Opsional)
        // DB::statement("ALTER TABLE maintenances MODIFY COLUMN result_status ENUM('pass', 'fail') NOT NULL");
    }
};