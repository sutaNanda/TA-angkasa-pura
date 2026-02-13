<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Asset;
use App\Models\User;
use App\Models\ChecklistTemplate;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    public function run()
    {
        // 1. AMBIL DATA MASTER YANG ADA
        // Pastikan minimal ada 1 Aset, 1 User, dan 1 Template
        $assets = Asset::all();
        $technician = User::first(); // Ambil user pertama saja sebagai teknisi
        $templates = ChecklistTemplate::with('items')->get();

        if ($assets->isEmpty() || $templates->isEmpty()) {
            $this->command->info('Skip Seeder: Harap buat minimal 1 Aset dan 1 Template SOP dulu.');
            return;
        }

        // ==========================================================
        // SKENARIO A: BUAT 15 DATA RIWAYAT "PASS" (NORMAL)
        // ==========================================================
        foreach(range(1, 15) as $i) {
            $asset = $assets->random();
            $template = $templates->random(); // Anggap aset ini cocok dengan template ini
            
            // Tanggal acak 1-30 hari lalu
            $date = Carbon::now()->subDays(rand(1, 30))->setHour(rand(8, 16));

            $maintenance = Maintenance::create([
                'asset_id' => $asset->id,
                'checklist_template_id' => $template->id,
                'user_id' => $technician->id,
                'schedule_date' => $date->format('Y-m-d'),
                'date' => $date, // Selesai dikerjakan
                'result_status' => 'pass',
                'status' => 'completed',
                'notes' => 'Unit beroperasi dengan normal, tidak ada kendala.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // Isi Detail Checklist (Semua Normal)
            foreach($template->items as $item) {
                MaintenanceDetail::create([
                    'maintenance_id' => $maintenance->id,
                    'checklist_item_id' => $item->id,
                    'answer' => $this->getNormalAnswer($item->type),
                    'is_abnormal' => false
                ]);
            }
        }

        // ==========================================================
        // SKENARIO B: BUAT 3 DATA RIWAYAT "FAIL" (RUSAK)
        // ==========================================================
        foreach(range(1, 3) as $i) {
            $asset = $assets->random();
            $template = $templates->random();
            $date = Carbon::now()->subDays(rand(1, 7));

            $maintenance = Maintenance::create([
                'asset_id' => $asset->id,
                'checklist_template_id' => $template->id,
                'user_id' => $technician->id,
                'schedule_date' => $date->format('Y-m-d'),
                'date' => $date,
                'result_status' => 'fail',
                'status' => 'completed',
                'notes' => 'Ditemukan indikasi kerusakan pada komponen utama.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // Isi Detail Checklist (Salah satu dibuat Abnormal)
            $items = $template->items;
            // Pilih 1 item acak untuk diset ERROR
            $errorItemIndex = rand(0, $items->count() - 1); 

            foreach($items as $index => $item) {
                $isError = ($index === $errorItemIndex);
                
                MaintenanceDetail::create([
                    'maintenance_id' => $maintenance->id,
                    'checklist_item_id' => $item->id,
                    'answer' => $isError ? $this->getBadAnswer($item->type) : $this->getNormalAnswer($item->type),
                    'is_abnormal' => $isError
                ]);
            }
        }

        // ==========================================================
        // SKENARIO C: BUAT 5 DATA "PENDING" (JADWAL HARI INI)
        // ==========================================================
        foreach(range(1, 5) as $i) {
            $asset = $assets->random();
            $template = $templates->random();

            Maintenance::create([
                'asset_id' => $asset->id,
                'checklist_template_id' => $template->id,
                'user_id' => null, // Belum ada teknisi yang ambil
                'schedule_date' => Carbon::now()->format('Y-m-d'), // Hari ini
                'date' => null, // Belum selesai
                'result_status' => null, // Belum ada hasil
                'status' => 'pending',
                'notes' => null,
            ]);
            // Tidak ada detail karena belum dikerjakan
        }
    }

    // Helper untuk jawaban normal dummy
    private function getNormalAnswer($type) {
        return match($type) {
            'pass_fail' => 'Pass',
            'checkbox' => 'Ya',
            'number' => rand(20, 40), // Angka aman
            'text' => 'Kondisi bersih',
            default => 'OK'
        };
    }

    // Helper untuk jawaban rusak dummy
    private function getBadAnswer($type) {
        return match($type) {
            'pass_fail' => 'Fail',
            'checkbox' => 'Tidak',
            'number' => rand(80, 100), // Overheat misal
            'text' => 'Kotor / Retak',
            default => 'Masalah'
        };
    }
}