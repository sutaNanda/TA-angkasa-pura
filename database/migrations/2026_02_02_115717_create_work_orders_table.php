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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // WO-001
            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('technician_id')->nullable()->constrained('users'); // Bisa null jika belum di-assign
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'completed', 'verified'])->default('open');
            $table->text('issue_description'); // Masalahnya apa
            $table->text('action_taken')->nullable(); // Apa yang dikerjakan
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            // Relasi ke logbook rutin (Nullable, karena bisa jadi lapor manual)
            $table->foreignId('maintenance_id')->nullable()->constrained('maintenances');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
