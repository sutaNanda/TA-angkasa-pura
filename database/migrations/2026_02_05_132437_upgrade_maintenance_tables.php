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
            $table->foreignId('checklist_template_id')->nullable()->after('asset_id')->constrained('checklist_templates');

            // Tambah jadwal (untuk Daylist)
            $table->date('schedule_date')->nullable()->after('date');

            // Tambah status pengerjaan
            $table->enum('status', ['pending', 'in_progress', 'completed', 'missed'])->default('pending')->after('result_status');

            // Ubah kolom date (waktu selesai) jadi boleh kosong (karena saat jadi jadwal, belum ada tanggal selesainya)
            $table->dateTime('date')->nullable()->change();

            // Ubah user_id jadi boleh kosong (karena saat dijadwalkan sistem, belum tau siapa teknisi yang ambil)
            $table->foreignId('user_id')->nullable()->change();
        });

        // 2. UPDATE TABEL MAINTENANCE_DETAILS (Jawaban)
        Schema::table('maintenance_details', function (Blueprint $table) {
            // Hapus koneksi ke Template (Salah Logika)
            // Cek nama constraint dulu di DB anda, biasanya maintenance_details_checklist_template_id_foreign
            $table->dropForeign(['checklist_template_id']);
            $table->dropColumn('checklist_template_id');

            // Tambah koneksi ke ITEM Pertanyaan (Benar Logika)
            $table->foreignId('checklist_item_id')->after('maintenance_id')->constrained('checklist_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Rollback logic (opsional, sesuaikan kebutuhan)
    }
};
