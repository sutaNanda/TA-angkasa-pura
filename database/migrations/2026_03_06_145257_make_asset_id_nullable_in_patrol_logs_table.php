<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('patrol_logs', function (Blueprint $table) {
            // Mengubah kolom asset_id menjadi boleh kosong (nullable)
            $table->unsignedBigInteger('asset_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('patrol_logs', function (Blueprint $table) {
            // Mengembalikan ke aturan semula jika di-rollback
            $table->unsignedBigInteger('asset_id')->nullable(false)->change();
        });
    }
};