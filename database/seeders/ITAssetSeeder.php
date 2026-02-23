<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Location;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class ITAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Dedicated IT Location Hierarchy
        // Gedung Pusat IT -> Lantai 2 -> Server Room Utama
        
        $building = Location::create([
            'name' => 'Gedung Pusat IT',
            'code' => 'IT-BUILDING',
            'type' => 'building',
            'parent_id' => null,
            'description' => 'Gedung khusus operasional IT dan Data Center'
        ]);

        $floor = Location::create([
            'name' => 'Lantai 2',
            'code' => 'IT-FL2',
            'type' => 'floor',
            'parent_id' => $building->id,
            'description' => 'Area teknis dan jaringan'
        ]);

        $room = Location::create([
            'name' => 'Server Room Utama',
            'code' => 'IT-SRV-MAIN',
            'type' => 'room',
            'parent_id' => $floor->id,
            'description' => 'Ruang server utama dengan pendingin presisi (Hanya Aset IT)'
        ]);

        $this->command->info("Locations created: {$building->name} > {$floor->name} > {$room->name}");

        // 2. Ensure Categories Exist
        $catServer = Category::firstOrCreate(['name' => 'Server & Storage'], ['description' => 'Perangkat server fisik dan storage']);
        $catNetwork = Category::firstOrCreate(['name' => 'Network (Switch/Router)'], ['description' => 'Perangkat jaringan aktif']);
        $catEndUser = Category::firstOrCreate(['name' => 'PC & Laptop'], ['description' => 'Perangkat komputasi end-user']);

        // 3. Create Dummy IT Assets
        $assets = [
            [
                'name' => 'Dell PowerEdge R750 #01',
                'category_id' => $catServer->id,
                'status' => 'active',
                'serial_number' => 'DELL-SRV-001-' . rand(1000, 9999),
                'purchase_date' => '2024-01-15',
                'description' => 'Primary Database Server',
                'image' => 'server_rack.jpg' // Assuming dummy image exists or handled
            ],
            [
                'name' => 'Dell PowerEdge R750 #02',
                'category_id' => $catServer->id,
                'status' => 'active',
                'serial_number' => 'DELL-SRV-002-' . rand(1000, 9999),
                'purchase_date' => '2024-01-15',
                'description' => 'Secondary Database Server (Replica)',
            ],
            [
                'name' => 'HP ProLiant DL380 Gen10',
                'category_id' => $catServer->id,
                'status' => 'maintenance',
                'serial_number' => 'HP-SRV-003-' . rand(1000, 9999),
                'purchase_date' => '2023-06-20',
                'description' => 'Application Server Legacy',
            ],
            [
                'name' => 'Cisco Catalyst 9300 Switch',
                'category_id' => $catNetwork->id,
                'status' => 'active',
                'serial_number' => 'CISCO-SW-001-' . rand(1000, 9999),
                'purchase_date' => '2024-02-01',
                'description' => 'Core Switch Lantai 2',
            ],
            [
                'name' => 'Mikrotik Cloud Core Router',
                'category_id' => $catNetwork->id,
                'status' => 'active',
                'serial_number' => 'MKT-RTR-001-' . rand(1000, 9999),
                'purchase_date' => '2024-02-01',
                'description' => 'Router Gateway Utama',
            ],
            [
                'name' => 'Synology NAS RS1221+',
                'category_id' => $catServer->id,
                'status' => 'active',
                'serial_number' => 'SYN-NAS-001-' . rand(1000, 9999),
                'purchase_date' => '2024-03-10',
                'description' => 'Backup Storage Server',
            ],
        ];

        foreach ($assets as $data) {
            Asset::create(array_merge($data, [
                'location_id' => $room->id, // All in Server Room
                'condition' => 'good'
            ]));
        }

        // Add some PC assets to a different room (e.g., IT Staff Room)
        $staffRoom = Location::create([
            'name' => 'Ruang Staff IT',
            'code' => 'IT-STAFF',
            'type' => 'room',
            'parent_id' => $floor->id,
            'description' => 'Ruang kerja staff IT'
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Asset::create([
                'name' => "PC Staff IT #0{$i}",
                'category_id' => $catEndUser->id,
                'location_id' => $staffRoom->id,
                'status' => 'active',
                'condition' => 'good',
                'serial_number' => "PC-IT-{$i}-" . rand(100, 999),
                'purchase_date' => '2023-11-01',
                'description' => 'High-spec PC for development'
            ]);
        }
        
        $this->command->info('IT Assets & Locations seeded successfully!');
    }
}
