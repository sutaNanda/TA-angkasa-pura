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
            $table->dropForeign(['category_id']);
            $table->dropForeign(['checklist_template_id']);
            $table->dropColumn(['category_id', 'checklist_template_id']);
            $table->json('template_configs')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropColumn('template_configs');
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('checklist_template_id')->nullable()->constrained();
        });
    }
};
