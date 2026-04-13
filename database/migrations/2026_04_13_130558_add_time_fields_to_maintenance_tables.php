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
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropColumn('start_time');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('scheduled_time');
        });
    }
};
