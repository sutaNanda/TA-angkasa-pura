<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ====================================================================
        // LANGKAH 1: BERSIHKAN TABEL LAMA (Rename & Drop Konflik FK)
        // ====================================================================

        // Cek kondisi: Apakah tabel lama belum direname?
        if (Schema::hasTable('checklist_templates') && !Schema::hasTable('checklist_items')) {

            // PENTING: Hapus Foreign Key lama dulu agar namanya (checklist_templates_category_id_foreign)
            // bisa dipakai oleh tabel baru nanti.
            Schema::table('checklist_templates', function (Blueprint $table) {
                // Drop FK berdasarkan nama kolom (Laravel otomatis cari nama constraintnya)
                $table->dropForeign(['category_id']);
            });

            // Baru di-rename
            Schema::rename('checklist_templates', 'checklist_items');
        }

        // Cek kondisi darurat: Jika migrasi sebelumnya gagal di tengah jalan,
        // mungkin tabel sudah jadi 'checklist_items' tapi FK-nya masih nyangkut.
        if (Schema::hasTable('checklist_items')) {
            try {
                Schema::table('checklist_items', function (Blueprint $table) {
                    // Coba drop constraint dengan nama spesifik jika masih ada
                    $table->dropForeign('checklist_templates_category_id_foreign');
                });
            } catch (\Exception $e) {
                // Abaikan error jika FK sudah tidak ada
            }
        }

        // ====================================================================
        // LANGKAH 2: BUAT TABEL BARU (Header Template)
        // ====================================================================
        if (!Schema::hasTable('checklist_templates')) {
            Schema::create('checklist_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Nama SOP
                $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly'])->default('daily');

                // Sekarang aman membuat FK ini karena yang lama sudah didrop di Langkah 1
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ====================================================================
        // LANGKAH 3: UPDATE TABEL ITEMS (Relasi ke Template Baru)
        // ====================================================================
        Schema::table('checklist_items', function (Blueprint $table) {
            // Cek dulu kolomnya sudah ada atau belum (untuk mencegah error duplicate column jika re-run)
            if (!Schema::hasColumn('checklist_items', 'checklist_template_id')) {
                $table->foreignId('checklist_template_id')->nullable()->after('id')->constrained('checklist_templates')->onDelete('cascade');
                $table->string('unit')->nullable()->after('type');
                $table->integer('order')->default(0)->after('unit');
            }
        });

        // ====================================================================
        // LANGKAH 4: MIGRASI DATA (Agar data lama tidak hilang)
        // ====================================================================
        // Masukkan item yatim piatu ke template default
        $anyItem = DB::table('checklist_items')->whereNull('checklist_template_id')->first();

        if ($anyItem) {
            // Buat 1 template default
            $defaultId = DB::table('checklist_templates')->insertGetId([
                'name' => 'Template Migrasi (Default)',
                'frequency' => 'daily',
                'description' => 'Template otomatis dari data lama',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update semua item lama ke template ini
            DB::table('checklist_items')->whereNull('checklist_template_id')->update([
                'checklist_template_id' => $defaultId
            ]);
        }
    }

    public function down(): void
    {
        // Rollback (Hati-hati)
        if (Schema::hasTable('checklist_items')) {
            Schema::table('checklist_items', function (Blueprint $table) {
                if (Schema::hasColumn('checklist_items', 'checklist_template_id')) {
                    $table->dropForeign(['checklist_template_id']);
                    $table->dropColumn(['checklist_template_id', 'unit', 'order']);
                }
                // Kembalikan FK category_id (ini agak tricky di rollback, biarkan null dulu gpp)
            });

            // Rename balik
            Schema::rename('checklist_items', 'checklist_templates');
        }

        Schema::dropIfExists('checklist_templates'); // Hapus tabel baru (jangan terbalik)
    }
};
