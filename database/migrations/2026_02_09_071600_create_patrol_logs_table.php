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
        Schema::create('patrol_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('checklist_template_id')->constrained()->onDelete('cascade');
            $table->json('inspection_data'); // Store answers as JSON
            $table->enum('status', ['normal', 'issue_found'])->default('normal');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('set null'); // Link to created work order if issue found
            $table->timestamps();

            // Indexes for faster queries
            $table->index('technician_id');
            $table->index('asset_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrol_logs');
    }
};
