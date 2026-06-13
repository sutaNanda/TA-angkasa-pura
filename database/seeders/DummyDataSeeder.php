<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\TechnicianGroup;
use App\Models\Location;
use App\Models\Category;
use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistItem;
use App\Models\MaintenancePlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA DEPARTEMEN (34 Departemen)
        $departments = [
            'Operation Control Dept.',
            'Operation Maintenance Dept.',
            'Customer Handling Services Dept.',
            'Cleanliness & Customer Improvement Dept.',
            'Airport Operation QC Dept.',
            'Airport Service QC Dept.',
            'Airport Maintenance QC Dept.',
            'Safety Management System & OHS Dept.',
            'Airside Operation Services Dept.',
            'Airport Rescue & Fire Fighting Dept.',
            'Protection Security Dept.',
            'Screening & Surveillance Security Dept.',
            'Terminal Services Support Dept.',
            'Non-Terminal Service Support Dept.',
            'Domestic Aero Commercial Dept.',
            'International Aero Commercial Dept.',
            'Retail & Duty Free Dept.',
            'F&B, Lounge, & Services Dept.',
            'Advertising & Landside Services Dept.',
            'Commercial Operation, Fitting Out, & Visual Merchandise Dept.',
            'Aero Support, Cargo, & Property Management Dept.',
            'Mechanical Services Dept.',
            'Electrical Services Dept.',
            'Electronic Services Dept.',
            'Airport Technology Services Dept.', // Dept Utama kita
            'Airport Airside Facilities Dept.',
            'Airport Landside Facilities Dept.',
            'Terminal Facilities Dept.',
            'Airport Environment Dept.',
            'General Services Dept.',
            'CSR Dept.',
            'Asset Management Dept.',
            'Branch Communication Dept.',
            'Legal Dept.'
        ];

        foreach ($departments as $deptName) {
            Department::firstOrCreate(['name' => $deptName]);
        }

        $tettDept = Department::where('name', 'Airport Technology Services Dept.')->first();
        $chsDept = Department::where('name', 'Customer Handling Services Dept.')->first();

        // 2. GRUP TEKNISI (2 Kelompok)
        $groupCoord = TechnicianGroup::firstOrCreate([
            'name' => 'Coordinator, Supervisor, & Engineer'
        ], [
            'description' => 'Tim Koordinator dan Engineer TETT (Shift P+)'
        ]);

        $groupOps = TechnicianGroup::firstOrCreate([
            'name' => 'Operation & Support (Tenaga Alih Daya)'
        ], [
            'description' => 'Tim Operasional Lapangan TETT'
        ]);

        // 3. PENGGUNA (Admin, Manajer, User, Teknisi)
        // Admin
        User::firstOrCreate(['email' => 'admin@aviatrack.my.id'], [
            'name' => 'Admin System',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $tettDept->id
        ]);

        // Department Head TETT (Sebagai Admin juga)
        User::firstOrCreate(['email' => 'hernani@aviatrack.my.id'], [
            'name' => 'Hernani Wahyu Nugraeni',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $tettDept->id
        ]);

        // Manajer (General Manager)
        User::firstOrCreate(['email' => 'gm@aviatrack.my.id'], [
            'name' => 'Nugroho Jati',
            'password' => Hash::make('password'),
            'role' => 'manajer',
            'department_id' => null
        ]);

        // User / Pelapor dari departemen lain
        User::firstOrCreate(['email' => 'staf.chs@aviatrack.my.id'], [
            'name' => 'Staf Customer Handling',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department_id' => $chsDept->id
        ]);

        // Teknisi - Coordinator Group
        $coords = [
            'I Putu Nana Sugianta Mandra',
            'Erfan Agil Putranto',
            'Asri Ebtami Kartika Dewi'
        ];
        foreach ($coords as $i => $name) {
            $emailName = strtolower(explode(' ', $name)[array_key_last(explode(' ', $name))]);
            User::firstOrCreate(['email' => $emailName . '@aviatrack.my.id'], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'department_id' => $tettDept->id,
                'technician_group_id' => $groupCoord->id
            ]);
        }

        // Teknisi - Operation & Support Group (21 orang)
        $ops = [
            'A.A Cahayadi P.', 'I Putu Sukma Antar Wijaya', 'I Putu Gede Tangkas Krisna Putra',
            'I Made Ari Pradipta', 'I Komang Suputra Wibawa', 'Benny Permana', 'Muchlis Al Khidziri',
            'I Made Dwika Kusuma P', 'I Dewa Made Angga Mayuna', 'Budi Maryanto', 'Gilang Ramadhan Putra',
            'I Gede Wira Ambhika', 'I Wayan Agus Prianata Putra', 'Romdon Tri Hamdani', 'I Wayan Agus Widnyana',
            'Wahyu Andika', 'Ayub Rahman Hakim', 'Komang Agus Trisna Adi P.', 'Cokorda Bagus Billy B',
            'I Wayan Putra Mahayana', 'I Made Burgatama'
        ];
        foreach ($ops as $i => $name) {
            // Ambil kata pertama untuk email, jika A.A ambil kata kedua
            $parts = explode(' ', $name);
            $emailPrefix = strtolower($parts[0] === 'I' || $parts[0] === 'A.A' ? ($parts[1] ?? $parts[0]) : $parts[0]);
            $emailPrefix = preg_replace('/[^a-z]/', '', $emailPrefix); // Bersihkan karakter aneh
            
            User::firstOrCreate(['email' => $emailPrefix . $i . '@aviatrack.my.id'], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'department_id' => $tettDept->id,
                'technician_group_id' => $groupOps->id
            ]);
        }

        // 4. LOKASI HIERARKIS
        $gedungTerminal = Location::firstOrCreate(['name' => 'Terminal I Gusti Ngurah Rai', 'type' => 'building']);
        $gedungKantor = Location::firstOrCreate(['name' => 'Gedung Perkantoran Angkasa Pura I', 'type' => 'building']);

        $areaDomestik = Location::firstOrCreate(['name' => 'Area Keberangkatan Domestik', 'parent_id' => $gedungTerminal->id, 'type' => 'area']);
        $areaIntl = Location::firstOrCreate(['name' => 'Boarding Lounge Internasional', 'parent_id' => $gedungTerminal->id, 'type' => 'area']);
        $lantai2Kantor = Location::firstOrCreate(['name' => 'Lantai 2', 'parent_id' => $gedungKantor->id, 'type' => 'floor']);

        $checkinIslandA = Location::firstOrCreate(['name' => 'Check-in Island A', 'parent_id' => $areaDomestik->id, 'type' => 'room']);
        $gate1Intl = Location::firstOrCreate(['name' => 'Gate 1', 'parent_id' => $areaIntl->id, 'type' => 'room']);
        $ruangServer = Location::firstOrCreate(['name' => 'Ruang Server TETT', 'parent_id' => $lantai2Kantor->id, 'type' => 'room']);

        // 5. KATEGORI ASET
        $catFids = Category::firstOrCreate(['name' => 'FIDS (Flight Information Display System)']);
        $catKiosk = Category::firstOrCreate(['name' => 'Self Check-in Kiosk']);
        $catNet = Category::firstOrCreate(['name' => 'Network Equipment']);

        // 6. DATA ASET
        $assetFids = Asset::firstOrCreate(['name' => 'Monitor FIDS Check-in A01'], [
            'category_id' => $catFids->id,
            'location_id' => $checkinIslandA->id,
            'status' => 'Normal',
            'serial_number' => 'FIDS-DOM-001'
        ]);

        $assetKiosk = Asset::firstOrCreate(['name' => 'Kiosk Check-in Int-01'], [
            'category_id' => $catKiosk->id,
            'location_id' => $gate1Intl->id,
            'status' => 'Normal',
            'serial_number' => 'KSK-INT-001'
        ]);

        $assetSwitch = Asset::firstOrCreate(['name' => 'Core Switch Cisco TETT'], [
            'category_id' => $catNet->id,
            'location_id' => $ruangServer->id,
            'status' => 'Normal',
            'serial_number' => 'NET-CS-001'
        ]);

        // 7. CHECKLIST TEMPLATE (SOP) & ITEMS
        $sopFids = ChecklistTemplate::firstOrCreate(['name' => 'Pengecekan Rutin FIDS'], [
            'category_id' => $catFids->id,
            'description' => 'SOP Pengecekan Layar FIDS Keberangkatan',
            'frequency' => 'daily'
        ]);
        if ($sopFids->items()->count() == 0) {
            ChecklistItem::create(['checklist_template_id' => $sopFids->id, 'question' => 'Layar menyala normal?', 'type' => 'pass_fail', 'order' => 1]);
            ChecklistItem::create(['checklist_template_id' => $sopFids->id, 'question' => 'Jadwal penerbangan terupdate realtime?', 'type' => 'pass_fail', 'order' => 2]);
            ChecklistItem::create(['checklist_template_id' => $sopFids->id, 'question' => 'Tidak ada cacat fisik/bergaris pada layar?', 'type' => 'pass_fail', 'order' => 3]);
        }

        $sopNet = ChecklistTemplate::firstOrCreate(['name' => 'Pengecekan Bulanan Core Switch'], [
            'category_id' => $catNet->id,
            'description' => 'SOP Pengecekan Core Switch Data Center',
            'frequency' => 'monthly'
        ]);
        if ($sopNet->items()->count() == 0) {
            ChecklistItem::create(['checklist_template_id' => $sopNet->id, 'question' => 'Lampu indikator power normal?', 'type' => 'pass_fail', 'order' => 1]);
            ChecklistItem::create(['checklist_template_id' => $sopNet->id, 'question' => 'Suhu perangkat stabil?', 'type' => 'pass_fail', 'order' => 2]);
        }

        // 8. JADWAL MAINTENANCE PLAN
        $planFids = MaintenancePlan::firstOrCreate(['name' => 'Patroli Harian FIDS Domestik'], [
            'target_type' => 'asset',
            'template_configs' => [['category_id' => $catFids->id, 'template_id' => $sopFids->id]],
            'frequency' => 'daily',
            'start_date' => now()->toDateString(),
            'is_active' => true
        ]);
        // Attach asset target
        DB::table('maintenance_plan_assets')->updateOrInsert(
            ['maintenance_plan_id' => $planFids->id, 'asset_id' => $assetFids->id]
        );
        // Tugaskan ke Grup Ops
        DB::table('maintenance_plan_group')->updateOrInsert(
            ['maintenance_plan_id' => $planFids->id, 'technician_group_id' => $groupOps->id],
            ['start_time' => '08:00:00']
        );

        $planNet = MaintenancePlan::firstOrCreate(['name' => 'Maintenance Bulanan Core Switch'], [
            'target_type' => 'asset',
            'template_configs' => [['category_id' => $catNet->id, 'template_id' => $sopNet->id]],
            'frequency' => 'monthly',
            'start_date' => now()->startOfMonth()->toDateString(),
            'is_active' => true
        ]);
        // Attach asset target
        DB::table('maintenance_plan_assets')->updateOrInsert(
            ['maintenance_plan_id' => $planNet->id, 'asset_id' => $assetSwitch->id]
        );
        // Tugaskan ke Grup Coordinator
        DB::table('maintenance_plan_group')->updateOrInsert(
            ['maintenance_plan_id' => $planNet->id, 'technician_group_id' => $groupCoord->id],
            ['start_time' => '10:00:00']
        );

    }
}
