<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Password default untuk semua akun: "password"
        $password = Hash::make('password');

        // 1. ADMIN (Akses Penuh)
        User::updateOrCreate(
            ['email' => 'admin@demo.com'], // Cek berdasarkan email
            [
                'name' => 'Administrator Utama',
                'password' => $password,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. TEKNISI (Akses Mobile / Lapangan)
        User::updateOrCreate(
            ['email' => 'teknisi@demo.com'],
            [
                'name' => 'Budi Santoso (Teknisi)',
                'password' => $password,
                'role' => 'teknisi',
                'email_verified_at' => now(),
            ]
        );

        // 3. MANAJER (Akses Laporan / Read Only Dashboard)
        User::updateOrCreate(
            ['email' => 'manajer@demo.com'],
            [
                'name' => 'Pak Manajer',
                'password' => $password,
                'role' => 'manajer',
                'email_verified_at' => now(),
            ]
        );

        // 4. USER BIASA (Staff Umum / Pelapor)
        User::updateOrCreate(
            ['email' => 'user@demo.com'],
            [
                'name' => 'Staff Umum',
                'password' => $password,
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Opsional: Tambah Teknisi Kedua untuk tes
        User::updateOrCreate(
            ['email' => 'teknisi2@demo.com'],
            [
                'name' => 'Agus Setiawan (Teknisi)',
                'password' => $password,
                'role' => 'teknisi',
                'email_verified_at' => now(),
            ]
        );
    }
}
