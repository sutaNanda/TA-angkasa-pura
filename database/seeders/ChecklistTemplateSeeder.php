<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistItem;
use App\Models\Category; // Pastikan model Category ada

class ChecklistTemplateSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ada Kategori dulu (Opsional, sesuaikan dengan data Category Anda)
        // Kita cari ID kategori berdasarkan nama, atau null jika tidak ada
        $catGenset = Category::where('name', 'like', '%Genset%')->first()->id ?? null;
        $catAC = Category::where('name', 'like', '%AC%')->first()->id ?? null;
        $catServer = Category::where('name', 'like', '%Server%')->orWhere('name', 'like', '%Komputer%')->first()->id ?? null;

        // ==========================================
        // 1. SOP HARIAN GENSET
        // ==========================================
        $template1 = ChecklistTemplate::create([
            'name' => 'Pengecekan Harian Genset',
            'category_id' => $catGenset,
            'frequency' => 'daily',
            'description' => 'Pastikan menggunakan Earplug saat memasuki ruang mesin. Cek parameter visual saja.'
        ]);

        $items1 = [
            ['question' => 'Level Bahan Bakar (Solar)', 'type' => 'pass_fail', 'unit' => null],
            ['question' => 'Tegangan Baterai / Aki', 'type' => 'number', 'unit' => 'Volt'],
            ['question' => 'Apakah ada kebocoran oli/air?', 'type' => 'pass_fail', 'unit' => null],
            ['question' => 'Suhu Air Radiator', 'type' => 'number', 'unit' => '°C'],
            ['question' => 'Switch Mode (Harus di Posisi AUTO)', 'type' => 'checkbox', 'unit' => null],
        ];

        foreach ($items1 as $item) {
            ChecklistItem::create(array_merge($item, ['checklist_template_id' => $template1->id]));
        }

        // ==========================================
        // 2. SOP MINGGUAN AC SPLIT
        // ==========================================
        $template2 = ChecklistTemplate::create([
            'name' => 'Maintenance Mingguan AC',
            'category_id' => $catAC,
            'frequency' => 'weekly',
            'description' => 'Pembersihan filter debu dan pengecekan fungsi remote.'
        ]);

        $items2 = [
            ['question' => 'Kondisi Filter Udara (Bersih/Kotor)', 'type' => 'pass_fail', 'unit' => null],
            ['question' => 'Suhu Output Udara Dingin', 'type' => 'number', 'unit' => '°C'],
            ['question' => 'Fungsi Swing / Louver', 'type' => 'pass_fail', 'unit' => null],
            ['question' => 'Apakah ada tetesan air bocor?', 'type' => 'checkbox', 'unit' => null], // Checkbox Ya = Ada Bocor (Fail logic nanti diatur)
            ['question' => 'Catatan Suara Kompresor', 'type' => 'text', 'unit' => null],
        ];

        foreach ($items2 as $item) {
            ChecklistItem::create(array_merge($item, ['checklist_template_id' => $template2->id]));
        }

        // ==========================================
        // 3. SOP BULANAN RUANG SERVER
        // ==========================================
        $template3 = ChecklistTemplate::create([
            'name' => 'Kebersihan & Suhu Ruang Server',
            'category_id' => $catServer,
            'frequency' => 'monthly',
            'description' => 'Cek kebersihan lantai, rak server, dan suhu ruangan.'
        ]);

        $items3 = [
            ['question' => 'Suhu Ruangan (Wajib 18-22)', 'type' => 'number', 'unit' => '°C'],
            ['question' => 'Kelembaban (Humidity)', 'type' => 'number', 'unit' => '%'],
            ['question' => 'Kondisi Kebersihan Lantai (Raised Floor)', 'type' => 'pass_fail', 'unit' => null],
            ['question' => 'Indikator Lampu Server (Ada Merah/Amber?)', 'type' => 'text', 'unit' => null],
            ['question' => 'Cek Kabel Semerawut', 'type' => 'pass_fail', 'unit' => null],
        ];

        foreach ($items3 as $item) {
            ChecklistItem::create(array_merge($item, ['checklist_template_id' => $template3->id]));
        }
    }
}
