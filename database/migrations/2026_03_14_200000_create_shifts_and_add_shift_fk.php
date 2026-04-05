<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create shifts master table
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Pagi, Siang, Sore, Malam
            $table->time('start_time');
            $table->time('end_time');
            $table->string('color', 50)->default('gray'); // For badge styling
            $table->timestamps();
        });

        // 2. Seed default shift data
        DB::table('shifts')->insert([
            [
                'name' => 'Pagi',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'color' => 'yellow',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Malam',
                'start_time' => '20:00:00',
                'end_time' => '08:00:00',
                'color' => 'purple',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Add shift_id FK to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('role')->constrained('shifts')->nullOnDelete();
        });

        // 4. Add shift_id FK to maintenance_plans
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('notes')->constrained('shifts')->nullOnDelete();
        });

        // 5. Add shift_id FK to patrol_logs
        Schema::table('patrol_logs', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('work_order_id')->constrained('shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patrol_logs', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });

        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });

        Schema::dropIfExists('shifts');
    }
};
