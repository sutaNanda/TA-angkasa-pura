<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Support\Str; // <--- 1. WAJIB IMPORT INI

class AssetSeeder extends Seeder
{
    public function run()
    {
        // 1. SIAPKAN KATEGORI
        // Tambahkan 'slug' => Str::slug('Nama Kategori') di array kedua
        
        $catMekanikal = Category::firstOrCreate(
            ['name' => 'Mekanikal & Mesin'],
            [
                'slug' => Str::slug('Mekanikal & Mesin'), // <--- TAMBAHAN
                'description' => 'Aset mesin berat, genset, pompa', 
                'icon' => 'fa-cogs'
            ]
        );
        
        $catElektrikal = Category::firstOrCreate(
            ['name' => 'Elektrikal & AC'],
            [
                'slug' => Str::slug('Elektrikal & AC'), // <--- TAMBAHAN
                'description' => 'Perangkat listrik, AC, Panel', 
                'icon' => 'fa-bolt'
            ]
        );

        $catIT = Category::firstOrCreate(
            ['name' => 'IT & Komputer'],
            [
                'slug' => Str::slug('IT & Komputer'), // <--- TAMBAHAN
                'description' => 'Server, PC, CCTV, Jaringan', 
                'icon' => 'fa-server'
            ]
        );

        $catFasilitas = Category::firstOrCreate(
            ['name' => 'Fasilitas Gedung'],
            [
                'slug' => Str::slug('Fasilitas Gedung'), // <--- TAMBAHAN
                'description' => 'Lift, Eskalator, Gate', 
                'icon' => 'fa-building'
            ]
        );

        // 2. SIAPKAN LOKASI
        $locBasement = Location::firstOrCreate(['name' => 'Basement & Utilitas']);
        $locLobby = Location::firstOrCreate(['name' => 'Lobby Utama']);
        $locServerRoom = Location::firstOrCreate(['name' => 'Ruang Server Lt.2']);
        $locRooftop = Location::firstOrCreate(['name' => 'Rooftop (Area Outdoor)']);

        // 3. BUAT DATA DUMMY ASET
        // (Bagian bawah sudah benar, tidak perlu diubah)

        Asset::create([
            'name' => 'Genset Silent Perkins 500kVA',
            'serial_number' => 'GEN-PK-2023-001',
            'category_id' => $catMekanikal->id,
            'location_id' => $locBasement->id,
            'status' => 'normal',
            'purchase_date' => '2023-01-15',
            'image' => null,
            'specifications' => json_encode([
                'Merk' => 'Perkins',
                'Kapasitas' => '500 kVA',
                'Tipe' => 'Silent Type',
                'Tangki Solar' => '500 Liter',
                'Tahun Pembuatan' => '2022'
            ]),
        ]);

        Asset::create([
            'name' => 'Main Jockey Pump Hydrant',
            'serial_number' => 'HYD-GR-9921',
            'category_id' => $catMekanikal->id,
            'location_id' => $locBasement->id,
            'status' => 'maintenance',
            'purchase_date' => '2022-05-20',
            'specifications' => json_encode([
                'Merk' => 'Grundfos',
                'Power' => '5.5 kW',
                'Flow Rate' => '500 GPM',
                'Tekanan' => '10 Bar'
            ]),
        ]);

        Asset::create([
            'name' => 'AC Precision Server 1',
            'serial_number' => 'AC-DK-SRV-01',
            'category_id' => $catElektrikal->id,
            'location_id' => $locServerRoom->id,
            'status' => 'normal',
            'purchase_date' => '2024-02-10',
            'specifications' => json_encode([
                'Merk' => 'Daikin',
                'Tipe' => 'Precision Air Conditioner',
                'Kapasitas' => '5 PK',
                'Refrigerant' => 'R410A'
            ]),
        ]);

        Asset::create([
            'name' => 'Rack Server Utama (Core)',
            'serial_number' => 'RCK-42U-001',
            'category_id' => $catIT->id,
            'location_id' => $locServerRoom->id,
            'status' => 'normal',
            'purchase_date' => '2021-11-01',
            'specifications' => json_encode([
                'Merk' => 'Indorack',
                'Ukuran' => '42U',
                'Isi' => 'Switch Core, Router Mikrotik, NAS'
            ]),
        ]);

        Asset::create([
            'name' => 'AC Cassette Lobby Timur',
            'serial_number' => 'AC-CS-LBY-02',
            'category_id' => $catElektrikal->id,
            'location_id' => $locLobby->id,
            'status' => 'rusak',
            'purchase_date' => '2020-08-17',
            'specifications' => json_encode([
                'Merk' => 'Panasonic',
                'Kapasitas' => '3 PK',
                'Model' => 'Cassette Ceiling'
            ]),
        ]);

        Asset::create([
            'name' => 'CCTV Dome Pintu Masuk',
            'serial_number' => 'CCTV-IK-005',
            'category_id' => $catIT->id,
            'location_id' => $locLobby->id,
            'status' => 'normal',
            'purchase_date' => '2023-06-01',
            'specifications' => json_encode([
                'Merk' => 'Hikvision',
                'Resolusi' => '4MP',
                'Tipe' => 'IP Camera Dome',
                'Fitur' => 'Night Vision'
            ]),
        ]);

        Asset::create([
            'name' => 'Unit Outdoor VRV System',
            'serial_number' => 'ODU-VRV-001',
            'category_id' => $catElektrikal->id,
            'location_id' => $locRooftop->id,
            'status' => 'normal',
            'purchase_date' => '2022-01-20',
            'specifications' => json_encode([
                'Merk' => 'Mitsubishi Electric',
                'Kapasitas Total' => '20 PK',
                'Kompresor' => 'Inverter Scroll'
            ]),
        ]);
        
        Asset::create([
            'name' => 'Lift Penumpang A (Ganjil)',
            'serial_number' => 'LFT-KONE-01',
            'category_id' => $catFasilitas->id,
            'location_id' => $locLobby->id,
            'status' => 'normal',
            'purchase_date' => '2019-05-20',
            'specifications' => json_encode([
                'Merk' => 'Kone',
                'Kapasitas' => '15 Orang / 1000kg',
                'Lantai' => 'GF - 10'
            ]),
        ]);
    }
}