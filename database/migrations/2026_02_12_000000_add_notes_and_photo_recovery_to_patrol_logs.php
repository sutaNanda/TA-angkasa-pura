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
        Schema::table('patrol_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('patrol_logs', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('patrol_logs', 'photo')) {
                $table->string('photo')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patrol_logs', function (Blueprint $table) {
            if (Schema::hasColumn('patrol_logs', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('patrol_logs', 'photo')) {
                $table->dropColumn('photo');
            }
        });
    }
};
