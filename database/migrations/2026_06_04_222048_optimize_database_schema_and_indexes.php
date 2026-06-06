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
        // 1. MIGRASI DATA LAMA (Data Copy)
        // Assets: Pindahkan 'image' ke 'images' (jika images null/kosong)
        if (Schema::hasColumn('assets', 'image') && Schema::hasColumn('assets', 'images')) {
            DB::table('assets')
                ->whereNotNull('image')
                ->where(function($query) {
                    $query->whereNull('images')->orWhere('images', '[]')->orWhere('images', '');
                })
                ->orderBy('id')
                ->chunk(100, function ($assets) {
                    foreach ($assets as $asset) {
                        DB::table('assets')->where('id', $asset->id)->update([
                            'images' => json_encode([$asset->image])
                        ]);
                    }
                });
        }

        // Patrol Logs: Pindahkan 'photo' ke 'photos'
        if (Schema::hasColumn('patrol_logs', 'photo') && Schema::hasColumn('patrol_logs', 'photos')) {
            DB::table('patrol_logs')
                ->whereNotNull('photo')
                ->where(function($query) {
                    $query->whereNull('photos')->orWhere('photos', '[]')->orWhere('photos', '');
                })
                ->orderBy('id')
                ->chunk(100, function ($logs) {
                    foreach ($logs as $log) {
                        DB::table('patrol_logs')->where('id', $log->id)->update([
                            'photos' => json_encode([$log->photo])
                        ]);
                    }
                });
        }

        // Work Orders: Pindahkan 'photo_before' -> 'photos_before' dan 'photo_after' -> 'photos_after'
        if (Schema::hasColumn('work_orders', 'photo_before') && Schema::hasColumn('work_orders', 'photos_before')) {
            DB::table('work_orders')
                ->whereNotNull('photo_before')
                ->where(function($query) {
                    $query->whereNull('photos_before')->orWhere('photos_before', '[]')->orWhere('photos_before', '');
                })
                ->orderBy('id')
                ->chunk(100, function ($wos) {
                    foreach ($wos as $wo) {
                        DB::table('work_orders')->where('id', $wo->id)->update([
                            'photos_before' => json_encode([$wo->photo_before])
                        ]);
                    }
                });
        }

        if (Schema::hasColumn('work_orders', 'photo_after') && Schema::hasColumn('work_orders', 'photos_after')) {
            DB::table('work_orders')
                ->whereNotNull('photo_after')
                ->where(function($query) {
                    $query->whereNull('photos_after')->orWhere('photos_after', '[]')->orWhere('photos_after', '');
                })
                ->orderBy('id')
                ->chunk(100, function ($wos) {
                    foreach ($wos as $wo) {
                        DB::table('work_orders')->where('id', $wo->id)->update([
                            'photos_after' => json_encode([$wo->photo_after])
                        ]);
                    }
                });
        }

        // 2. DROP REDUNDANT COLUMNS
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'image')) $table->dropColumn('image');
        });

        Schema::table('patrol_logs', function (Blueprint $table) {
            if (Schema::hasColumn('patrol_logs', 'photo')) $table->dropColumn('photo');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'photo_before')) $table->dropColumn('photo_before');
            if (Schema::hasColumn('work_orders', 'photo_after')) $table->dropColumn('photo_after');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'date')) $table->dropColumn('date');
            if (Schema::hasColumn('maintenances', 'schedule_date')) $table->dropColumn('schedule_date');
        });

        // 3. ADD INDEXES & COMPOSITE INDEXES
        Schema::table('work_orders', function (Blueprint $table) {
            $table->index(['status', 'deleted_at'], 'idx_wo_status_deleted');
            $table->index('priority', 'idx_wo_priority');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->index(['status', 'deleted_at'], 'idx_asset_status_deleted');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'deleted_at'], 'idx_user_role_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Indexes
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('idx_wo_status_deleted');
            $table->dropIndex('idx_wo_priority');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('idx_asset_status_deleted');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_user_role_deleted');
        });

        // Restore Columns
        Schema::table('assets', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('patrol_logs', function (Blueprint $table) {
            $table->string('photo')->nullable();
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->datetime('date')->nullable();
            $table->date('schedule_date')->nullable();
        });
    }
};
