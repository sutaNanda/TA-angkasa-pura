<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\User;
use App\Models\Maintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class WorkOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. SIAPKAN DATA PENDUKUNG
        $assets = Asset::all();
        
        // Cek Teknisi (Fix Error: Create if empty)
        $technicians = User::where('role', 'technician')->get();
        if ($technicians->isEmpty()) {
            $this->command->info('Membuat user Teknisi Dummy karena belum ada...');
            $tech = User::create([
                'name' => 'Teknisi Demo',
                'email' => 'teknisi@demo.com',
                'password' => Hash::make('password'),
                'role' => 'technician'
            ]);
            $technicians = collect([$tech]); // Masukkan ke collection agar bisa di-random
        }

        // Ambil Admin (Jika tidak ada, pakai user pertama apapun role-nya)
        $admin = User::where('role', 'admin')->first() ?? User::first();
        
        // Ambil Maintenance yang statusnya FAIL
        $failedMaintenances = Maintenance::where('result_status', 'fail')->get();

        if ($assets->isEmpty()) {
            $this->command->info('Skip: Tidak ada data Aset. Jalankan AssetSeeder dulu.');
            return;
        }

        // ==========================================================
        // SKENARIO 1: TIKET OTOMATIS DARI HASIL PATROLI (FAIL)
        // ==========================================================
        foreach ($failedMaintenances as $index => $maintenance) {
            $date = Carbon::parse($maintenance->date);
            
            // Cek apakah tiket untuk maintenance ini sudah ada biar gak duplikat
            if(WorkOrder::where('maintenance_id', $maintenance->id)->exists()) continue;

            WorkOrder::create([
                'ticket_number' => 'WO-' . $date->format('Ymd') . '-AUTO' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'asset_id' => $maintenance->asset_id,
                'technician_id' => $technicians->random()->id,
                'reported_by' => null, 
                'priority' => 'high',
                'status' => 'in_progress', 
                'issue_description' => 'Ditemukan anomali saat patroli rutin. ' . ($maintenance->notes ?? 'Cek detail log.'),
                'maintenance_id' => $maintenance->id,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // ==========================================================
        // SKENARIO 2: TIKET MANUAL - STATUS OPEN (BARU)
        // ==========================================================
        $issues = ['AC Bocor air menetes', 'Lampu indikator mati', 'Suara mesin kasar', 'Kabel terkelupas'];
        
        foreach(range(1, 3) as $i) {
            WorkOrder::create([
                // Nomor tiket digenerate otomatis oleh Model
                'asset_id' => $assets->random()->id,
                'technician_id' => null, 
                'reported_by' => $admin->id,
                'priority' => 'medium',
                'status' => 'open',
                'issue_description' => $issues[array_rand($issues)],
                'maintenance_id' => null, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==========================================================
        // SKENARIO 3: TIKET MENUNGGU SPAREPART (PENDING)
        // ==========================================================
        WorkOrder::create([
            'asset_id' => $assets->random()->id,
            'technician_id' => $technicians->first()->id,
            'reported_by' => $admin->id,
            'priority' => 'medium',
            'status' => 'pending_part',
            'issue_description' => 'Filter udara sobek, butuh penggantian.',
            'action_taken' => 'Sudah melakukan pemesanan part ke vendor.',
            'maintenance_id' => null,
            'created_at' => now()->subDays(2),
            'updated_at' => now(),
        ]);

        // ==========================================================
        // SKENARIO 4: TIKET SELESAI (BUTUH VERIFIKASI)
        // ==========================================================
        foreach(range(1, 2) as $i) {
            WorkOrder::create([
                'ticket_number' => 'WO-' . now()->subDay()->format('Ymd') . '-DONE' . $i,
                'asset_id' => $assets->random()->id,
                'technician_id' => $technicians->random()->id,
                'reported_by' => $admin->id,
                'priority' => 'high',
                'status' => 'completed',
                'issue_description' => 'Suhu ruang server panas > 28 derajat.',
                'action_taken' => 'Membersihkan evaporator dan cek freon. Suhu normal kembali 20 derajat.',
                'photo_before' => 'https://via.placeholder.com/300x200.png?text=Before+Kotor',
                'photo_after' => 'https://via.placeholder.com/300x200.png?text=After+Bersih',
                'completed_at' => now()->subHours(2),
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ]);
        }

        // ==========================================================
        // SKENARIO 5: TIKET SUDAH DITUTUP (VERIFIED/HISTORY)
        // ==========================================================
        foreach(range(1, 5) as $i) {
            $date = now()->subDays(rand(5, 30)); 
            
            WorkOrder::create([
                'ticket_number' => 'WO-' . $date->format('Ymd') . '-HIST' . $i,
                'asset_id' => $assets->random()->id,
                'technician_id' => $technicians->random()->id,
                'reported_by' => $admin->id,
                'priority' => 'low',
                'status' => 'verified',
                'issue_description' => 'Pengecekan rutin permintaan user.',
                'action_taken' => 'Unit dicek dan berfungsi normal.',
                'completed_at' => $date->copy()->addHours(2),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}