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
        // 1. Assets
        Schema::table('assets', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image');
        });

        // 2. Work Orders
        Schema::table('work_orders', function (Blueprint $table) {
            $table->json('photos_before')->nullable()->after('photo_before');
            $table->json('photos_after')->nullable()->after('photo_after');
        });

        // 3. Patrol Logs
        Schema::table('patrol_logs', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('photo');
        });

        // 4. Work Order Histories
        Schema::table('work_order_histories', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('photo');
        });

        // Migrate Existing Data
        if (Schema::hasColumn('assets', 'image')) {
            DB::table('assets')->whereNotNull('image')->where('image', '!=', '')->orderBy('id')->chunk(100, function($rows) {
                foreach($rows as $row) {
                    DB::table('assets')->where('id', $row->id)->update(['images' => json_encode([$row->image])]);
                }
            });
        }
        
        if (Schema::hasColumn('work_orders', 'photo_before')) {
            DB::table('work_orders')->whereNotNull('photo_before')->where('photo_before', '!=', '')->orderBy('id')->chunk(100, function($rows) {
                foreach($rows as $row) {
                    DB::table('work_orders')->where('id', $row->id)->update(['photos_before' => json_encode([$row->photo_before])]);
                }
            });
        }

        if (Schema::hasColumn('work_orders', 'photo_after')) {
            DB::table('work_orders')->whereNotNull('photo_after')->where('photo_after', '!=', '')->orderBy('id')->chunk(100, function($rows) {
                foreach($rows as $row) {
                    DB::table('work_orders')->where('id', $row->id)->update(['photos_after' => json_encode([$row->photo_after])]);
                }
            });
        }

        if (Schema::hasColumn('patrol_logs', 'photo')) {
            DB::table('patrol_logs')->whereNotNull('photo')->where('photo', '!=', '')->orderBy('id')->chunk(100, function($rows) {
                foreach($rows as $row) {
                    DB::table('patrol_logs')->where('id', $row->id)->update(['photos' => json_encode([$row->photo])]);
                }
            });
        }

        if (Schema::hasColumn('work_order_histories', 'photo')) {
            DB::table('work_order_histories')->whereNotNull('photo')->where('photo', '!=', '')->orderBy('id')->chunk(100, function($rows) {
                foreach($rows as $row) {
                    DB::table('work_order_histories')->where('id', $row->id)->update(['photos' => json_encode([$row->photo])]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('images');
        });
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['photos_before', 'photos_after']);
        });
        Schema::table('patrol_logs', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
        Schema::table('work_order_histories', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
    }
};
