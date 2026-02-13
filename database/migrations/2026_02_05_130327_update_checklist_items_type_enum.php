<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kita menggunakan RAW SQL karena mengubah ENUM lewat Schema Builder Laravel sering bermasalah
        // Kita tambahkan 'pass_fail' dan 'checkbox' ke dalam daftar yang diperbolehkan.
        // Kita juga tetap menyimpan 'boolean' agar data lama (jika ada) tidak rusak.

        DB::statement("ALTER TABLE checklist_items MODIFY COLUMN type ENUM('pass_fail', 'number', 'text', 'checkbox', 'boolean') NOT NULL DEFAULT 'pass_fail'");
    }

    public function down(): void
    {
        // Kembalikan ke definisi awal (Hati-hati, data 'pass_fail' akan error jika di-rollback)
        // DB::statement("ALTER TABLE checklist_items MODIFY COLUMN type ENUM('boolean', 'number', 'text') NOT NULL");
    }
};
