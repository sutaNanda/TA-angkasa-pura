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
        Schema::table('users', function (Blueprint $table) {
            // requires_password_reset (default false)
            if (!Schema::hasColumn('users', 'requires_password_reset')) {
                $table->boolean('requires_password_reset')->default(false)->after('password');
            }

            // division_id (nullable, foreign key)
            if (!Schema::hasColumn('users', 'division_id')) {
                $table->foreignId('division_id')->nullable()->after('role')->constrained('divisions')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'division_id')) {
                $table->dropForeign(['division_id']);
                $table->dropColumn('division_id');
            }
            if (Schema::hasColumn('users', 'requires_password_reset')) {
                $table->dropColumn('requires_password_reset');
            }
        });
    }
};
