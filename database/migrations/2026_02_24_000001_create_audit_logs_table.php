<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');         // login, logout, create, update, delete, verify, assign
            $table->string('module');         // Asset, WorkOrder, User, Maintenance, etc.
            $table->text('description');      // Human-readable description
            $table->text('old_data')->nullable();   // JSON of old values (for updates)
            $table->text('new_data')->nullable();   // JSON of new values (for creates/updates)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
