<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // 1. Type Segregation (Untuk filter cepat)
            $table->enum('type', ['building', 'floor', 'room', 'area'])
                  ->default('room')
                  ->after('name')
                  ->index();

            // 2. Materialized Path (Untuk breadcrumb cepat)
            $table->string('path')->nullable()->after('parent_id')->index(); // Contoh: 1/5/12

            // 3. Hierarchy Level (Untuk tahu kedalaman)
            $table->tinyInteger('level')->default(0)->after('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['type', 'path', 'level']);
        });
    }
};
