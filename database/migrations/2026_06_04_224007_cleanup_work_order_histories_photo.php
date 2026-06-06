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
        // 1. Data Transfer
        if (Schema::hasColumn('work_order_histories', 'photo') && Schema::hasColumn('work_order_histories', 'photos')) {
            DB::table('work_order_histories')
                ->whereNotNull('photo')
                ->where(function($query) {
                    $query->whereNull('photos')->orWhere('photos', '[]')->orWhere('photos', '');
                })
                ->orderBy('id')
                ->chunk(100, function ($histories) {
                    foreach ($histories as $history) {
                        DB::table('work_order_histories')->where('id', $history->id)->update([
                            'photos' => json_encode([$history->photo])
                        ]);
                    }
                });
        }

        // 2. Drop Column
        Schema::table('work_order_histories', function (Blueprint $table) {
            if (Schema::hasColumn('work_order_histories', 'photo')) {
                $table->dropColumn('photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_histories', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('description');
        });
    }
};
