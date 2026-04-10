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
        Schema::table('maintenances', function (Blueprint $blueprint) {
            $blueprint->foreignId('location_id')->nullable()->after('checklist_template_id')->constrained('locations')->nullOnDelete();
            $blueprint->json('target_asset_ids')->nullable()->after('location_id');
            
            // asset_id will be nullable since we might have multi-asset tasks associated with a location
            $blueprint->unsignedBigInteger('asset_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['location_id']);
            $blueprint->dropColumn(['location_id', 'target_asset_ids']);
            $blueprint->unsignedBigInteger('asset_id')->nullable(false)->change();
        });
    }
};
