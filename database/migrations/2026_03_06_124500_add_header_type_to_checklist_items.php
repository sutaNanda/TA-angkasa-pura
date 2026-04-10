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
        // Add 'header' to the ENUM list for checklist_items
        DB::statement("ALTER TABLE checklist_items MODIFY COLUMN type ENUM('pass_fail', 'number', 'text', 'checkbox', 'boolean', 'header') NOT NULL DEFAULT 'pass_fail'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous ENUM (omitting 'header')
        DB::statement("ALTER TABLE checklist_items MODIFY COLUMN type ENUM('pass_fail', 'number', 'text', 'checkbox', 'boolean') NOT NULL DEFAULT 'pass_fail'");
    }
};
