<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\WorkOrderHistory; // [NEW]

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // SAFETY CHECK: Skip jika data sudah ada
        if (DB::table('users')->where('email', 'admin@example.com')->exists()) {
            $this->command->warn('⚠️  Data sudah ada! Seeder di-skip untuk mencegah duplicate.');
            $this->command->info('💡 Jika ingin reset ulang, jalankan: php artisan migrate:fresh');
            return;
        }

        $this->command->info('🏢 Seeding Tech Tower Office Building...');

        // ========================================
        // 1. USERS
        // ========================================
        $this->command->info('👤 Creating users...');
        
        DB::table('users')->insert([
            [
                'name' => 'Admin Manager',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Teknisi',
                'email' => 'tech@example.com',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rudi Teknisi',
                'email' => 'rudi@example.com',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Teknisi',
                'email' => 'siti@example.com',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // ========================================
        // 2. LOCATIONS (3-Level Hierarchy)
        // ========================================
        $this->command->info('📍 Creating location hierarchy...');
        
        // ROOT: Gedung
        $techTower = DB::table('locations')->insertGetId([
            'name' => 'Tech Tower',
            'code' => 'LOC-TOWER',
            'parent_id' => null,
            'description' => 'Gedung perkantoran 3 lantai dengan fasilitas IT lengkap',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LEVEL 1: Lantai 1
        $lt1 = DB::table('locations')->insertGetId([
            'name' => 'Lantai 1',
            'code' => 'LOC-L1',
            'parent_id' => $techTower,
            'description' => 'Lantai Ground - Area Publik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LEVEL 2: Ruangan di Lantai 1
        $lobby = DB::table('locations')->insertGetId([
            'name' => 'Lobby & Reception',
            'code' => 'LOC-LOBBY',
            'parent_id' => $lt1,
            'description' => 'Area penerimaan tamu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pantry1 = DB::table('locations')->insertGetId([
            'name' => 'Pantry Lt.1',
            'code' => 'LOC-PANTRY1',
            'parent_id' => $lt1,
            'description' => 'Dapur karyawan lantai 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LEVEL 1: Lantai 2
        $lt2 = DB::table('locations')->insertGetId([
            'name' => 'Lantai 2',
            'code' => 'LOC-L2',
            'parent_id' => $techTower,
            'description' => 'Lantai Operasional - IT & Staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LEVEL 2: Ruangan di Lantai 2
        $serverRoom = DB::table('locations')->insertGetId([
            'name' => 'Ruang Server',
            'code' => 'LOC-SERVER',
            'parent_id' => $lt2,
            'description' => 'Data Center - Akses Terbatas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffRoom = DB::table('locations')->insertGetId([
            'name' => 'Ruang Staff IT',
            'code' => 'LOC-STAFF',
            'parent_id' => $lt2,
            'description' => 'Workspace tim IT (20 orang)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $meetingRoom = DB::table('locations')->insertGetId([
            'name' => 'Meeting Room A',
            'code' => 'LOC-MEETING',
            'parent_id' => $lt2,
            'description' => 'Ruang rapat kapasitas 12 orang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LEVEL 1: Lantai 3
        $lt3 = DB::table('locations')->insertGetId([
            'name' => 'Lantai 3',
            'code' => 'LOC-L3',
            'parent_id' => $techTower,
            'description' => 'Lantai Manajemen & Utilitas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LEVEL 2: Ruangan di Lantai 3
        $directorRoom = DB::table('locations')->insertGetId([
            'name' => 'Ruang Direktur',
            'code' => 'LOC-DIRECTOR',
            'parent_id' => $lt3,
            'description' => 'Kantor eksekutif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rooftop = DB::table('locations')->insertGetId([
            'name' => 'Rooftop Utility',
            'code' => 'LOC-ROOFTOP',
            'parent_id' => $lt3,
            'description' => 'Area genset, water tank, AC outdoor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ========================================
        // 3. CATEGORIES (dengan Icon FontAwesome)
        // ========================================
        $this->command->info('📦 Creating asset categories...');
        
        $catNetwork = DB::table('categories')->insertGetId([
            'name' => 'Network Device',
            'slug' => 'network-device',
            'icon' => 'fa-solid fa-network-wired',
            'description' => 'Router, Switch, Firewall, Server',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catHVAC = DB::table('categories')->insertGetId([
            'name' => 'HVAC & Cooling',
            'slug' => 'hvac-cooling',
            'icon' => 'fa-solid fa-fan',
            'description' => 'AC Split, AC Presisi, Chiller',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catFurniture = DB::table('categories')->insertGetId([
            'name' => 'Furniture & Interior',
            'slug' => 'furniture',
            'icon' => 'fa-solid fa-couch',
            'description' => 'Meja, Kursi, Lemari, Sofa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catSafety = DB::table('categories')->insertGetId([
            'name' => 'Safety Equipment',
            'slug' => 'safety',
            'icon' => 'fa-solid fa-fire-extinguisher',
            'description' => 'APAR, Emergency Light, Smoke Detector',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catPower = DB::table('categories')->insertGetId([
            'name' => 'Power & Electric',
            'slug' => 'power-electric',
            'icon' => 'fa-solid fa-bolt',
            'description' => 'UPS, Genset, Panel Listrik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catComputer = DB::table('categories')->insertGetId([
            'name' => 'Computer & Peripheral',
            'slug' => 'computer',
            'icon' => 'fa-solid fa-laptop',
            'description' => 'Laptop, Desktop, Monitor, Printer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ========================================
        // 4. ASSETS (15 Aset Realistis)
        // ========================================
        $this->command->info('💼 Creating assets...');
        
        DB::table('assets')->insert([
            // === RUANG SERVER ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Rak Server Dell PowerEdge R740',
                'serial_number' => 'SRV-RACK-001',
                'category_id' => $catNetwork,
                'location_id' => $serverRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Processor' => 'Intel Xeon Gold 6130',
                    'RAM' => '128GB DDR4',
                    'Storage' => '4x 2TB SSD RAID 10',
                    'Network' => '4x 10Gb Ethernet'
                ]),
                'purchase_date' => '2023-05-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'AC Presisi Emerson Liebert PDX 10KW',
                'serial_number' => 'HVAC-SRV-001',
                'category_id' => $catHVAC,
                'location_id' => $serverRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Cooling Capacity' => '10 KW',
                    'Refrigerant' => 'R410A',
                    'Target Temp' => '18-22°C',
                    'Humidity Control' => '40-60%'
                ]),
                'purchase_date' => '2023-06-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'UPS APC Smart-UPS 10KVA',
                'serial_number' => 'UPS-SRV-001',
                'category_id' => $catPower,
                'location_id' => $serverRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Capacity' => '10 KVA / 8 KW',
                    'Battery Runtime' => '30 menit (full load)',
                    'Input Voltage' => '220V',
                    'Output Voltage' => '220V ±5%'
                ]),
                'purchase_date' => '2023-05-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Cisco Catalyst 9300 Switch',
                'serial_number' => 'NET-SW-001',
                'category_id' => $catNetwork,
                'location_id' => $serverRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Ports' => '48x 1Gb + 4x 10Gb SFP+',
                    'Switching Capacity' => '176 Gbps',
                    'PoE Budget' => '740W',
                    'Firmware' => 'IOS-XE 17.6.3'
                ]),
                'purchase_date' => '2023-07-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RUANG STAFF IT ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Laptop Dell Latitude 5420 #01',
                'serial_number' => 'LAP-IT-001',
                'category_id' => $catComputer,
                'location_id' => $staffRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Processor' => 'Intel Core i7-1185G7',
                    'RAM' => '16GB DDR4',
                    'Storage' => '512GB NVMe SSD',
                    'Display' => '14" FHD'
                ]),
                'purchase_date' => '2024-01-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Monitor LG UltraWide 34" #01',
                'serial_number' => 'MON-IT-001',
                'category_id' => $catComputer,
                'location_id' => $staffRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Size' => '34 inch',
                    'Resolution' => '3440x1440 UWQHD',
                    'Panel Type' => 'IPS',
                    'Refresh Rate' => '75Hz'
                ]),
                'purchase_date' => '2024-01-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'AC Split Daikin 2PK',
                'serial_number' => 'HVAC-STAFF-001',
                'category_id' => $catHVAC,
                'location_id' => $staffRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Cooling Capacity' => '2 PK (18.000 BTU)',
                    'Refrigerant' => 'R32',
                    'Energy Rating' => '4 Star',
                    'Inverter' => 'Yes'
                ]),
                'purchase_date' => '2023-08-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === MEETING ROOM ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Projector Epson EB-2250U',
                'serial_number' => 'PROJ-MTG-001',
                'category_id' => $catComputer,
                'location_id' => $meetingRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Brightness' => '5000 Lumens',
                    'Resolution' => 'WUXGA 1920x1200',
                    'Lamp Life' => '10000 hours',
                    'Connectivity' => 'HDMI, VGA, USB'
                ]),
                'purchase_date' => '2023-09-05',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Meja Meeting Kayu Jati 3m',
                'serial_number' => 'FURN-MTG-001',
                'category_id' => $catFurniture,
                'location_id' => $meetingRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Material' => 'Kayu Jati Solid',
                    'Dimensi' => '300cm x 120cm x 75cm',
                    'Kapasitas' => '12 orang',
                    'Finishing' => 'Natural Varnish'
                ]),
                'purchase_date' => '2023-04-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === LOBBY ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Sofa Tamu L-Shape Kulit',
                'serial_number' => 'FURN-LOBBY-001',
                'category_id' => $catFurniture,
                'location_id' => $lobby,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Material' => 'Kulit Sintetis Premium',
                    'Warna' => 'Cokelat Tua',
                    'Kapasitas' => '6 orang',
                    'Dimensi' => '280cm x 180cm'
                ]),
                'purchase_date' => '2023-03-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'APAR Powder 9Kg - Lobby',
                'serial_number' => 'SAFE-LOBBY-001',
                'category_id' => $catSafety,
                'location_id' => $lobby,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Type' => 'ABC Dry Chemical Powder',
                    'Capacity' => '9 Kg',
                    'Fire Class' => 'A, B, C',
                    'Expired Date' => '2026-12-31'
                ]),
                'purchase_date' => '2024-01-05',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === ROOFTOP ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Genset Perkins 150KVA',
                'serial_number' => 'GEN-ROOF-001',
                'category_id' => $catPower,
                'location_id' => $rooftop,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Brand' => 'Perkins',
                    'Capacity' => '150 KVA / 120 KW',
                    'Fuel Type' => 'Solar (Diesel)',
                    'Tank Capacity' => '500 Liter',
                    'Auto Start' => 'Yes (ATS)'
                ]),
                'purchase_date' => '2023-02-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'AC Outdoor Unit VRV Daikin 20HP',
                'serial_number' => 'HVAC-ROOF-001',
                'category_id' => $catHVAC,
                'location_id' => $rooftop,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Type' => 'VRV (Variable Refrigerant Volume)',
                    'Capacity' => '20 HP',
                    'Refrigerant' => 'R410A',
                    'Indoor Units' => '12 unit terhubung'
                ]),
                'purchase_date' => '2023-08-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === PANTRY ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Kulkas 2 Pintu Sharp 450L',
                'serial_number' => 'APPL-PANTRY-001',
                'category_id' => $catFurniture,
                'location_id' => $pantry1,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Capacity' => '450 Liter',
                    'Type' => '2 Door Refrigerator',
                    'Energy Rating' => '3 Star',
                    'Features' => 'Inverter, Plasmacluster'
                ]),
                'purchase_date' => '2023-10-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === RUANG DIREKTUR ===
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Meja Direktur Executive Mahogany',
                'serial_number' => 'FURN-DIR-001',
                'category_id' => $catFurniture,
                'location_id' => $directorRoom,
                'status' => 'normal',
                'image' => null,
                'specifications' => json_encode([
                    'Material' => 'Mahogany Solid Wood',
                    'Dimensi' => '200cm x 100cm x 75cm',
                    'Finishing' => 'Glossy Varnish',
                    'Features' => 'Built-in Cable Management'
                ]),
                'purchase_date' => '2023-03-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. CHECKLIST TEMPLATES & ITEMS
        // ========================================
        $this->command->info('📋 Creating checklist templates...');
        
        // Template 1: Daily AC Inspection
        $tplDailyAC = DB::table('checklist_templates')->insertGetId([
            'name' => 'Inspeksi Harian AC & HVAC',
            'description' => 'Pengecekan visual harian untuk semua unit AC',
            'frequency' => 'daily',
            'category_id' => $catHVAC,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Items untuk Daily AC
        $acQuestions = [
            ['question' => 'Apakah unit AC hidup (ON)?', 'type' => 'boolean'],
            ['question' => 'Suhu display normal sesuai setting?', 'type' => 'boolean'],
            ['question' => 'Input Suhu Aktual (°C)', 'type' => 'number', 'unit' => '°C'],
            ['question' => 'Apakah ada indikasi bocor air/refrigerant?', 'type' => 'boolean'],
            ['question' => 'Suara mesin halus/normal (tidak berisik)?', 'type' => 'boolean'],
            ['question' => 'Filter udara bersih (tidak berdebu)?', 'type' => 'boolean'],
        ];

        foreach ($acQuestions as $index => $q) {
            DB::table('checklist_items')->insert([
                'checklist_template_id' => $tplDailyAC,
                'question' => $q['question'],
                'type' => $q['type'],
                'unit' => $q['unit'] ?? null,
                'order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Template 2: Weekly Server Check
        $tplWeeklyServer = DB::table('checklist_templates')->insertGetId([
            'name' => 'Pengecekan Mingguan Server & Network',
            'description' => 'Inspeksi rutin perangkat IT kritis',
            'frequency' => 'weekly',
            'category_id' => $catNetwork,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $serverQuestions = [
            ['question' => 'Server status: Running normal?', 'type' => 'boolean'],
            ['question' => 'CPU Usage (%)', 'type' => 'number', 'unit' => '%'],
            ['question' => 'RAM Usage (%)', 'type' => 'number', 'unit' => '%'],
            ['question' => 'Disk Space tersisa (GB)', 'type' => 'number', 'unit' => 'GB'],
            ['question' => 'Suhu ruang server normal (18-24°C)?', 'type' => 'boolean'],
            ['question' => 'Kabel management rapi?', 'type' => 'boolean'],
            ['question' => 'Backup berjalan sukses minggu ini?', 'type' => 'boolean'],
        ];

        foreach ($serverQuestions as $index => $q) {
            DB::table('checklist_items')->insert([
                'checklist_template_id' => $tplWeeklyServer,
                'question' => $q['question'],
                'type' => $q['type'],
                'unit' => $q['unit'] ?? null,
                'order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Template 3: Monthly APAR
        $tplMonthlyAPAR = DB::table('checklist_templates')->insertGetId([
            'name' => 'Inspeksi Bulanan APAR & Safety',
            'description' => 'Pengecekan kelayakan alat pemadam kebakaran',
            'frequency' => 'monthly',
            'category_id' => $catSafety,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $aparQuestions = [
            ['question' => 'Pressure gauge di zona hijau?', 'type' => 'boolean'],
            ['question' => 'Segel/pin safety masih utuh?', 'type' => 'boolean'],
            ['question' => 'Tabung tidak berkarat/penyok?', 'type' => 'boolean'],
            ['question' => 'Selang tidak retak/bocor?', 'type' => 'boolean'],
            ['question' => 'Label expired date masih valid?', 'type' => 'boolean'],
            ['question' => 'Lokasi mudah diakses (tidak terhalang)?', 'type' => 'boolean'],
        ];

        foreach ($aparQuestions as $index => $q) {
            DB::table('checklist_items')->insert([
                'checklist_template_id' => $tplMonthlyAPAR,
                'question' => $q['question'],
                'type' => $q['type'],
                'unit' => null,
                'order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Template 4: Weekly Genset
        $tplWeeklyGenset = DB::table('checklist_templates')->insertGetId([
            'name' => 'Maintenance Mingguan Genset',
            'description' => 'Pengecekan rutin genset dan test run',
            'frequency' => 'weekly',
            'category_id' => $catPower,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gensetQuestions = [
            ['question' => 'Level solar/fuel cukup (>50%)?', 'type' => 'boolean'],
            ['question' => 'Level oli mesin aman (dipstick)?', 'type' => 'boolean'],
            ['question' => 'Tegangan aki (Volt)', 'type' => 'number', 'unit' => 'V'],
            ['question' => 'Test run 10 menit berhasil?', 'type' => 'boolean'],
            ['question' => 'Output voltage stabil (220V ±10V)?', 'type' => 'boolean'],
            ['question' => 'Tidak ada kebocoran oli/fuel?', 'type' => 'boolean'],
        ];

        foreach ($gensetQuestions as $index => $q) {
            DB::table('checklist_items')->insert([
                'checklist_template_id' => $tplWeeklyGenset,
                'question' => $q['question'],
                'type' => $q['type'],
                'unit' => $q['unit'] ?? null,
                'order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        // ========================================
        // 6. MAINTENANCE PLANS (Aturan Otomatis)
        // ========================================
        $this->command->info('⚙️  Creating maintenance plans...');
        
        $planDailyHVAC = DB::table('maintenance_plans')->insertGetId([
            'name' => 'Patroli Harian HVAC',
            'category_id' => $catHVAC,
            'checklist_template_id' => $tplDailyAC,
            'frequency' => 'daily',
            'start_date' => now()->startOfMonth(),
            'notes' => 'Semua unit AC/HVAC wajib dicek setiap hari untuk memastikan suhu optimal',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planWeeklyNetwork = DB::table('maintenance_plans')->insertGetId([
            'name' => 'Inspeksi Mingguan Network Device',
            'category_id' => $catNetwork,
            'checklist_template_id' => $tplWeeklyServer,
            'frequency' => 'weekly',
            'start_date' => now()->startOfMonth(),
            'notes' => 'Cek performa server, switch, dan infrastruktur jaringan setiap Senin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planMonthlySafety = DB::table('maintenance_plans')->insertGetId([
            'name' => 'Pengecekan Bulanan Safety Equipment',
            'category_id' => $catSafety,
            'checklist_template_id' => $tplMonthlyAPAR,
            'frequency' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'notes' => 'Inspeksi APAR dan alat safety setiap tanggal 1',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planWeeklyPower = DB::table('maintenance_plans')->insertGetId([
            'name' => 'Maintenance Mingguan Power System',
            'category_id' => $catPower,
            'checklist_template_id' => $tplWeeklyGenset,
            'frequency' => 'weekly',
            'start_date' => now()->startOfMonth(),
            'notes' => 'Test run genset dan cek UPS setiap Sabtu pagi',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ========================================
        // 7. GENERATE TUGAS HARI INI (Simulasi)
        // ========================================
        $this->command->info('📅 Generating today\'s maintenance tasks...');
        
        $today = now()->format('Y-m-d');
        
        // Ambil semua aset HVAC untuk tugas harian
        $hvacAssets = DB::table('assets')->where('category_id', $catHVAC)->get();
        
        foreach($hvacAssets as $asset) {
            DB::table('maintenances')->insert([
                'asset_id' => $asset->id,
                'maintenance_plan_id' => $planDailyHVAC, // Plan Harian HVAC
                'checklist_template_id' => $tplDailyAC,
                'technician_id' => null,
                'scheduled_date' => $today,
                'type' => 'preventive',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ambil beberapa aset Network untuk tugas mingguan (jika hari ini Senin)
        if (now()->dayOfWeek === 1) { // Monday
            $networkAssets = DB::table('assets')
                ->where('category_id', $catNetwork)
                ->limit(2)
                ->get();
            
            foreach($networkAssets as $asset) {
                DB::table('maintenances')->insert([
                    'asset_id' => $asset->id,
                    'maintenance_plan_id' => $planWeeklyNetwork, // Plan Mingguan Network
                    'checklist_template_id' => $tplWeeklyServer,
                    'technician_id' => null,
                    'scheduled_date' => $today,
                    'type' => 'preventive',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ========================================
        // 8. RIWAYAT PATROLI (PATROL LOGS)
        // ========================================
        $this->command->info('📜 Generating Patrol Logs (History)...');

        $teknisi = DB::table('users')->where('email', 'tech@example.com')->first();
        $techRudi = DB::table('users')->where('email', 'rudi@example.com')->first();
        $techSiti = DB::table('users')->where('email', 'siti@example.com')->first();
        $bulanLalu = now()->subMonth();

        // Cari ID asset untuk history
        $assetACServer = DB::table('assets')->where('name', 'like', '%AC Presisi%')->value('id');
        $assetProjector = DB::table('assets')->where('name', 'like', '%Projector%')->value('id');
        $assetSwitch = DB::table('assets')->where('name', 'like', '%Cisco%')->value('id');
        $assetGenset = DB::table('assets')->where('name', 'like', '%Genset%')->value('id');

        // Mock Inspection Data (Checklist)
        $checklistDataNormal = json_encode([
            ['question' => 'Suhu Ruangan (18-22 C)', 'answer' => '20 C', 'is_abnormal' => false],
            ['question' => 'Kebersihan Filter', 'answer' => 'Bersih', 'is_abnormal' => false],
            ['question' => 'Suara Kipas', 'answer' => 'Halus', 'is_abnormal' => false],
        ]);

        $checklistDataIssue = json_encode([
            ['question' => 'Suhu Ruangan (18-22 C)', 'answer' => '28 C', 'is_abnormal' => true, 'note' => 'Panas banget'],
            ['question' => 'Kebersihan Filter', 'answer' => 'Kotor', 'is_abnormal' => true],
            ['question' => 'Suara Kipas', 'answer' => 'Berisik', 'is_abnormal' => true],
        ]);

        // Log 1: AC Presisi Server (Issue Found)
        // [HANDOVER SCENARIO TARGET]
        $acPresisiPatrolId = null;
        if ($assetACServer) {
            $acPresisiPatrolId = DB::table('patrol_logs')->insertGetId([
                'technician_id' => $teknisi->id ?? 1, // Budi
                'asset_id' => $assetACServer,
                'location_id' => 1, // Asumsi Server Room
                'checklist_template_id' => $tplDailyAC,
                'inspection_data' => $checklistDataIssue,
                'status' => 'issue_found',
                'created_at' => $bulanLalu->copy()->addDays(2),
                'updated_at' => $bulanLalu->copy()->addDays(2),
            ]);
        }

        // Log 2: Projector Overheat (Issue Found)
        $projectorPatrolId = null;
        if ($assetProjector) {
            $projectorPatrolId = DB::table('patrol_logs')->insertGetId([
                'technician_id' => $teknisi->id ?? null,
                'asset_id' => $assetProjector,
                'location_id' => 2, // Asumsi Meeting Room
                'checklist_template_id' => $tplWeeklyServer, // Gunakan template Server sebegai placeholder
                'inspection_data' => json_encode([
                    ['question' => 'Kondisi Fisik', 'answer' => 'Panas Berlebih', 'is_abnormal' => true],
                    ['question' => 'Fungsi Tampilan', 'answer' => 'Mati Sendiri', 'is_abnormal' => true]
                ]),
                'status' => 'issue_found',
                'created_at' => $bulanLalu->copy()->addDays(10),
                'updated_at' => $bulanLalu->copy()->addDays(10),
            ]);
        }

        // Log 3: Genset Check (Normal)
        if ($assetGenset) {
            DB::table('patrol_logs')->insert([
                'technician_id' => $teknisi->id ?? null,
                'asset_id' => $assetGenset,
                'location_id' => 11, // Rooftop
                'checklist_template_id' => $tplWeeklyGenset,
                'inspection_data' => $checklistDataNormal,
                'status' => 'normal',
                'created_at' => $bulanLalu->copy()->addDays(5),
                'updated_at' => $bulanLalu->copy()->addDays(5),
            ]);
        }

        // Log 4: AC Split Staff (Issue Found) - Rudi
        $assetACStaff = DB::table('assets')->where('name', 'like', '%AC Split Daikin%')->value('id');
        $acStaffPatrolId = null; // Variable to store ID
        if ($assetACStaff) {
             $acStaffPatrolId = DB::table('patrol_logs')->insertGetId([
                'technician_id' => $techRudi->id ?? 2,
                'asset_id' => $assetACStaff,
                'location_id' => 7, // Staff Room
                'checklist_template_id' => $tplDailyAC,
                'inspection_data' => json_encode([
                    ['question' => 'Suhu Dingin', 'answer' => 'Kurang Dingin', 'is_abnormal' => true, 'note' => 'Bunyi berisik'],
                    ['question' => 'Lampu Indikator', 'answer' => 'Kedip', 'is_abnormal' => true]
                ]),
                'status' => 'issue_found', // Ganti ke Issue Found
                'created_at' => $bulanLalu->copy()->addDays(3),
                'updated_at' => $bulanLalu->copy()->addDays(3),
            ]);
        }

        // Log 5: Kulkas Pantry (Issue Found) - Siti
        $assetKulkas = DB::table('assets')->where('name', 'like', '%Kulkas%')->value('id');
        $kulkasPatrolId = null;
        if ($assetKulkas) {
            $kulkasPatrolId = DB::table('patrol_logs')->insertGetId([
                'technician_id' => $techSiti->id ?? 2,
                'asset_id' => $assetKulkas,
                'location_id' => 4, // Pantry
                // Tpl sementara karena belum ada template kulkas di seeder
                'checklist_template_id' => $tplDailyAC, 
                'inspection_data' => json_encode([
                    ['question' => 'Suhu Dingin', 'answer' => 'Tidak Dingin', 'is_abnormal' => true, 'note' => 'Kompresor mati'],
                    ['question' => 'Lampu Interior', 'answer' => 'Nyala', 'is_abnormal' => false]
                ]),
                'status' => 'issue_found',
                'created_at' => $bulanLalu->copy()->addDays(7),
                'updated_at' => $bulanLalu->copy()->addDays(7),
            ]);
        }

        // 9. WORK ORDERS (TIKET)
        $this->command->info('🔧 Generating Work Orders...');

        $manager = DB::table('users')->where('email', 'admin@example.com')->first();

        // WO 1: Handover Example (AC Staff)
        // Skenario: Budi mengerjakan, tapi sparepart kurang, jadi di-handover (lepas tugas)
        DB::table('work_orders')->insert([
            'ticket_number' => 'WO-' . $bulanLalu->format('Ymd') . '-0099',
            'asset_id' => $assetACStaff ?? 7,
            'technician_id' => null, // RELEASED / HANDOVER STATUS
            'reported_by' => $manager->id,
            'priority' => 'medium',
            'status' => 'handover', // Status Handover agar muncul distinct
            'source' => 'manual_ticket',
            'issue_description' => "AC bunyi berisik di ruang staff saat jam kerja.\n\n[HANDOVER - Budi Teknisi @ " . $bulanLalu->copy()->addDays(4)->format('d M H:i') . "]:\nCatatan: Sudah cleaning filter dan cek blower. Masih berisik, kemungkinan bearing motor fan aus. Sparepart bearing belum tersedia di gudang.",
            'action_taken' => 'Pengecekan awal, cleaning filter.',
            'created_at' => $bulanLalu->copy()->addDays(3),
            'updated_at' => $bulanLalu->copy()->addDays(4),
        ]);

        $woId = DB::getPdo()->lastInsertId();

        // [LINKING] Update Patrol Log 1 (AC Presisi) karena user klik item paling bawah
        if ($acPresisiPatrolId) {
            DB::table('patrol_logs')->where('id', $acPresisiPatrolId)->update(['work_order_id' => $woId]);
        }
        
        // Link juga AC Staff kalau ada, biar aman
        if ($acStaffPatrolId) {
             // Buat WO Baru untuk Ac staff (Duplicate process for safety, or just leave it)
        }

        // 10. GENERATE HISTORY UNTUK WORK ORDER (TIMELINE)
        $this->command->info('📜 Generating Work Order History (Timeline)...');

        // History 1: Tiket Dibuat (Oleh Admin/System)
        WorkOrderHistory::create([
            'work_order_id' => $woId,
            'user_id' => $manager->id,
            'action' => 'created',
            'description' => 'Tiket dibuat manual berdasarkan laporan user.',
            'created_at' => $bulanLalu->copy()->addDays(3)->addHour(1),
        ]);

        // History 2: Diambil oleh Budi (In Progress)
        WorkOrderHistory::create([
            'work_order_id' => $woId,
            'user_id' => $teknisi->id, // Budi
            'action' => 'in_progress',
            'description' => 'Mulai pengecekan unit. Indikasi awal bearing fan motor bunyi.',
            'created_at' => $bulanLalu->copy()->addDays(3)->addHours(2),
        ]);

        // History 3: Handover oleh Budi (Handover)
        WorkOrderHistory::create([
            'work_order_id' => $woId,
            'user_id' => $teknisi->id, // Budi
            'action' => 'handover', 
            'description' => 'Catatan: Sudah cleaning filter dan cek blower. Masih berisik, kemungkinan bearing motor fan aus. Sparepart bearing belum tersedia di gudang.',
            'created_at' => $bulanLalu->copy()->addDays(4)->addHours(1),
        ]);


        // [NEW] WO 2: Projector (In Progress)
        if ($projectorPatrolId) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . $bulanLalu->format('Ymd') . '-0100',
                'asset_id' => $assetProjector,
                'technician_id' => $techRudi->id,
                'reported_by' => $manager->id,
                'priority' => 'high',
                'status' => 'in_progress',
                'source' => 'patrol',
                'issue_description' => "Projector mati sendiri karena overheat.",
                'created_at' => $bulanLalu->copy()->addDays(10)->addHour(),
                'updated_at' => $bulanLalu->copy()->addDays(10)->addHours(2),
            ]);
            $wo2Id = DB::getPdo()->lastInsertId();
            DB::table('patrol_logs')->where('id', $projectorPatrolId)->update(['work_order_id' => $wo2Id]);

            // History WO 2
            WorkOrderHistory::create([
                'work_order_id' => $wo2Id,
                'user_id' => $manager->id,
                'action' => 'created',
                'description' => 'Tiket otomatis dari Patrol Log #' . $projectorPatrolId,
                'created_at' => $bulanLalu->copy()->addDays(10)->addHour(),
            ]);
            WorkOrderHistory::create([
                'work_order_id' => $wo2Id,
                'user_id' => $techRudi->id,
                'action' => 'in_progress',
                'description' => 'Sedang bongkar unit untuk cek kipas.',
                'created_at' => $bulanLalu->copy()->addDays(10)->addHours(2),
            ]);
        }

        // [NEW] WO 3: Kulkas (Completed)
        if ($kulkasPatrolId) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . $bulanLalu->format('Ymd') . '-0101',
                'asset_id' => $assetKulkas,
                'technician_id' => $techSiti->id,
                'reported_by' => $manager->id,
                'priority' => 'medium',
                'status' => 'completed',
                'source' => 'patrol',
                'issue_description' => "Kulkas tidak dingin (Kompresor mati).",
                'created_at' => $bulanLalu->copy()->addDays(7)->addHour(),
                'updated_at' => $bulanLalu->copy()->addDays(8),
            ]);
            $wo3Id = DB::getPdo()->lastInsertId();
            DB::table('patrol_logs')->where('id', $kulkasPatrolId)->update(['work_order_id' => $wo3Id]);

            // History WO 3
             WorkOrderHistory::create([
                'work_order_id' => $wo3Id,
                'user_id' => $manager->id,
                'action' => 'created',
                'description' => 'Tiket otomatis dari Patrol Log #' . $kulkasPatrolId,
                'created_at' => $bulanLalu->copy()->addDays(7)->addHour(),
            ]);
             WorkOrderHistory::create([
                'work_order_id' => $wo3Id,
                'user_id' => $techSiti->id,
                'action' => 'completed',
                'description' => 'Kompresor diganti baru. Suhu normal kembali.',
                'created_at' => $bulanLalu->copy()->addDays(8),
            ]);
        }



        // ========================================
        // 9. WORK ORDERS (TIKET PERBAIKAN) - EXPANDED FOR DASHBOARD
        // ========================================
        $this->command->info('🔧 Generating expanded work orders for Dashboard...');

        $adminId = DB::table('users')->where('role', 'admin')->value('id');
        $techId = DB::table('users')->where('role', 'teknisi')->value('id');

        // Asset IDs
        $idPrinter = DB::table('assets')->where('name', 'like', '%Printer%')->value('id');
        $idAC = DB::table('assets')->where('name', 'like', '%AC Split%')->value('id');
        $idCisco = DB::table('assets')->where('name', 'like', '%Cisco%')->value('id');
        $idGenset = DB::table('assets')->where('name', 'like', '%Genset%')->value('id');
        $idUps = DB::table('assets')->where('name', 'like', '%UPS%')->value('id');
        $idLaptop = DB::table('assets')->where('name', 'like', '%Laptop%')->value('id');

        // 1. TIKET OPEN (MANUAL - HIGH)
        if ($idPrinter) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . now()->subDays(2)->format('Ymd') . '-0001',
                'asset_id' => $idPrinter,
                'reported_by' => $adminId,
                'technician_id' => null, // Belum diambil
                'issue_description' => 'URGENT: Printer Finance macet total laporan dari Bu Ani. Segera cek!',
                'priority' => 'high',
                'status' => 'open',
                'source' => 'manual_ticket',
                'created_at' => now()->subDays(2)->hour(9),
                'updated_at' => now()->subDays(2)->hour(9),
            ]);
        }

        // 2. TIKET IN PROGRESS (PATROL - HIGH)
        if ($idAC) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . now()->subDays(1)->format('Ymd') . '-0002',
                'asset_id' => $idAC,
                'reported_by' => $techId,
                'technician_id' => $techId, // Sedang dikerjakan
                'issue_description' => 'Temuan Patroli: Freon bocor dan menetes ke rack server. Perlu pengelasan pipa.',
                'priority' => 'high',
                'status' => 'in_progress',
                'source' => 'patrol',
                'created_at' => now()->subDays(1)->hour(8),
                'updated_at' => now()->subDays(1)->hour(10),
            ]);
        }

        // 3. TIKET PENDING PART (MANUAL - MEDIUM)
        if ($idCisco) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . now()->subDays(3)->format('Ymd') . '-0003',
                'asset_id' => $idCisco,
                'reported_by' => $adminId,
                'technician_id' => $techId,
                'issue_description' => 'Port 24 mati. Menunggu modul SFP pengganti dari vendor.',
                'priority' => 'medium',
                'status' => 'pending_part',
                'source' => 'manual_ticket',
                'created_at' => now()->subDays(3)->hour(14),
                'updated_at' => now()->subDays(3)->hour(15),
            ]);
        }

        // 4. TIKET COMPLETED (PATROL - LOW)
        if ($idGenset) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . now()->subDays(5)->format('Ymd') . '-0004',
                'asset_id' => $idGenset,
                'reported_by' => $techId,
                'technician_id' => $techId,
                'issue_description' => 'Temuan Patroli: Aki genset level air rendah.',
                'action_taken' => 'Isi ulang air aki sampai level normal.',
                'priority' => 'low',
                'status' => 'completed',
                'source' => 'patrol',
                'created_at' => now()->subDays(5)->hour(8),
                'updated_at' => now()->subDays(5)->hour(9),
                'completed_at' => now()->subDays(5)->hour(9),
            ]);
        }

        // 5. TIKET VERIFIED (MANUAL - MEDIUM)
        if ($idUps) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . now()->subDays(7)->format('Ymd') . '-0005',
                'asset_id' => $idUps,
                'reported_by' => $adminId,
                'technician_id' => $techId,
                'issue_description' => 'UPS beep terus menerus kata security.',
                'action_taken' => 'Ganti baterai UPS yang sudah soak.',
                'priority' => 'medium',
                'status' => 'verified',
                'source' => 'manual_ticket',
                'created_at' => now()->subDays(7)->hour(10),
                'updated_at' => now()->subDays(7)->hour(12),
                'completed_at' => now()->subDays(7)->hour(12),
            ]);
        }

        // 6. TIKET OPEN BARU (MANUAL - LOW)
        if ($idLaptop) {
            DB::table('work_orders')->insert([
                'ticket_number' => 'WO-' . now()->format('Ymd') . '-0006',
                'asset_id' => $idLaptop,
                'reported_by' => $adminId,
                'technician_id' => null,
                'issue_description' => 'User minta install ulang Windows karena lambat.',
                'priority' => 'low',
                'status' => 'open',
                'source' => 'manual_ticket',
                'created_at' => now()->hour(13),
                'updated_at' => now()->hour(13),
            ]);
        }
        
        $this->command->info('✅ Seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - 2 Users created');
        $this->command->info('   - 11 Locations (3-level hierarchy)');
        $this->command->info('   - 6 Categories');
        $this->command->info('   - 15 Assets');
        $this->command->info('   - 4 Checklist Templates');
        $this->command->info('   - 4 Maintenance Plans');
        $this->command->info('   - ' . DB::table('maintenances')->count() . ' Maintenance tasks for today');
    }
}