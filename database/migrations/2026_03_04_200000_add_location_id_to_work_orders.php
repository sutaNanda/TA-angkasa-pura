<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Add location_id column
            $table->foreignId('location_id')->nullable()->after('asset_id')->constrained('locations')->nullOnDelete();
        });

        // Make asset_id nullable - drop FK first, modify, re-add FK
        // We use raw SQL for safer FK handling across MySQL versions
        Schema::table('work_orders', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['asset_id']);
        });

        Schema::table('work_orders', function (Blueprint $table) {
            // Make asset_id nullable
            $table->unsignedBigInteger('asset_id')->nullable()->change();
            // Re-add FK as nullable
            $table->foreign('asset_id')->references('id')->on('assets')->nullOnDelete();
        });

        // Backfill: For existing work orders with asset_id, set location_id from asset's location
        DB::statement('
            UPDATE work_orders 
            SET location_id = (SELECT location_id FROM assets WHERE assets.id = work_orders.asset_id)
            WHERE asset_id IS NOT NULL AND location_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });

        // Revert asset_id to NOT NULL
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable(false)->change();
            $table->foreign('asset_id')->references('id')->on('assets');
        });
    }
};
