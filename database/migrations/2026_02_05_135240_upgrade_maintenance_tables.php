<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. UPDATE TABEL MAINTENANCES (Header)
        Schema::table('maintenances', function (Blueprint $table) {
            // Tambah kolom SOP agar tau ini pakai checklist yang mana
            if (!Schema::hasColumn('maintenances', 'checklist_template_id')) {
                $table->foreignId('checklist_template_id')->nullable()->after('asset_id')->constrained('checklist_templates');
            }

            // Tambah jadwal (untuk Daylist)
            if (!Schema::hasColumn('maintenances', 'schedule_date')) {
                $table->date('schedule_date')->nullable()->after('date');
            }

            // Tambah status pengerjaan
            if (!Schema::hasColumn('maintenances', 'status')) {
                $table->enum('status', ['pending', 'in_progress', 'completed', 'missed'])->default('pending')->after('result_status');
            }

            // Ubah kolom date jadi nullable
            $table->dateTime('date')->nullable()->change(); 
            // Ubah user_id jadi nullable
            $table->foreignId('user_id')->nullable()->change();
        });

        // 2. UPDATE TABEL MAINTENANCE_DETAILS (Jawaban)
        Schema::table('maintenance_details', function (Blueprint $table) {
            // Hapus koneksi ke Template (Salah Logika) jika masih ada
            if (Schema::hasColumn('maintenance_details', 'checklist_template_id')) {
                // Drop Foreign Key dulu (nama constraint mungkin beda, kita coba drop column langsung biasanya FK ikut error kalau tidak dihandle, tapi coba cara aman)
                try {
                    $table->dropForeign(['checklist_template_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('checklist_template_id');
            }

            // Tambah koneksi ke ITEM Pertanyaan (Benar Logika)
            if (!Schema::hasColumn('maintenance_details', 'checklist_item_id')) {
                $table->foreignId('checklist_item_id')->after('maintenance_id')->constrained('checklist_items')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        // Kosongkan saja agar aman
    }
};