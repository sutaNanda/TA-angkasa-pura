<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistItem;
use App\Models\MaintenancePlan;
use Illuminate\Support\Str;

class SoftwareExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Kategori Khusus Software jika belum ada
        $category = Category::firstOrCreate(
            ['name' => 'Software & Lisensi'],
            ['description' => 'Kategori khusus untuk aplikasi, lisensi, dan layanan SaaS']
        );

        // 2. Buat Contoh Data Aset Software (Tanpa Lokasi)
        $asset1 = Asset::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Zoom Pro Tahunan - Dept. IT',
            'serial_number' => 'ZOOM-2026-IT-001',
            'category_id' => $category->id,
            'location_id' => null, // TANPA LOKASI
            'status' => 'normal',
            'purchase_date' => now()->subMonths(2)->format('Y-m-d'),
            'specifications' => [
                'Vendor' => 'Zoom Video Communications',
                'License Key' => 'XXXX-YYYY-ZZZZ-1234',
                'Jumlah Seat' => '10 User',
                'Masa Aktif' => 'Sampai 1 Januari 2027',
                'Tipe Langganan' => 'Annual SaaS',
                'PIC' => 'Bpk. Budi (Manager IT)'
            ],
            // 'images' => ['software/zoom_invoice.png'] // Kosongkan saja untuk tes
        ]);

        $asset2 = Asset::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Windows Server 2022 Datacenter',
            'serial_number' => 'WIN-SVR-2022-DC',
            'category_id' => $category->id,
            'location_id' => null, // TANPA LOKASI
            'status' => 'normal',
            'purchase_date' => now()->subYears(1)->format('Y-m-d'),
            'specifications' => [
                'Vendor' => 'Microsoft',
                'Product Key' => 'A1B2C-D3E4F-G5H6I-J7K8L-M9N0P',
                'Lisensi' => 'Perpetual (Sekali Beli)',
                'Kegunaan' => 'Server Virtualisasi Proxmox Host'
            ]
        ]);

        // 3. Buat Template Pengecekan (Checklist Template) Khusus Software
        $template = ChecklistTemplate::create([
            'name' => 'Audit & Perawatan Software Bulanan',
            'description' => 'SOP untuk memverifikasi lisensi, keamanan, dan pemakaian aset perangkat lunak (Virtual).',
        ]);

        // 4. Isi Pertanyaan Pengecekan / Checklist Items
        ChecklistItem::insert([
            [
                'template_id' => $template->id,
                'question' => 'Apakah lisensi masih aktif dan belum mendekati masa kadaluarsa (expired)?',
                'type' => 'radio',
                'options' => json_encode(['Masih Panjang', 'Mendekati Expired', 'Sudah Expired']),
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'template_id' => $template->id,
                'question' => 'Verifikasi Jumlah Pemakai (Seat Usage) di dashboard admin:',
                'type' => 'text',
                'options' => null,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'template_id' => $template->id,
                'question' => 'Apakah Software / Layanan tersebut telah di-update ke versi terbaru/patch keamanan terkini?',
                'type' => 'radio',
                'options' => json_encode(['Sudah Terupdate', 'Belum Ada Update', 'Perlu Segera Diupdate']),
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'template_id' => $template->id,
                'question' => 'Apakah terdapat log error atau komplain terkait software/layanan ini bulan sebelumnya?',
                'type' => 'radio',
                'options' => json_encode(['Tidak Ada', 'Ada Komplain Minor', 'Downtime Besar']),
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);

        // 5. Buat Jadwal Perawatan Otomatis (Maintenance Plan)
        // Set agar Teknisi melakukan pengecekan ini 1 Bulan Sekali (Every 1 Month)
        $plan = MaintenancePlan::create([
            'title' => 'Audit Rutin Software Mingguan/Bulanan',
            'description' => 'Teknisi diwajibkan melakukan audit status langganan dan keamanan software tanpa harus ke lokasi fisik.',
            'checklist_template_id' => $template->id,
            'frequency_type' => 'monthly', // Setiap Bulan
            'frequency_interval' => 1,
            'next_due_date' => now()->addDays(2)->format('Y-m-d'), // Misal jadwalnya lusa
            'is_active' => true,
        ]);

        // Hubungkan plan ke aset software tadi
        $plan->assets()->attach([$asset1->id, $asset2->id]);
    }
}
