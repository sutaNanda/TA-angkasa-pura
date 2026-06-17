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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('shift')->nullable()->after('technician_id');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('shift')->nullable()->after('executed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('shift');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('shift');
        });
    }
};
