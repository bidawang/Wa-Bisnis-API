<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::create([
            'nama'     => 'Admin Toko',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'no_hp'    => '081234567890',
            'status'   => 'aktif',
            'alamat'   => 'Jl. Admin No. 1',
        ]);

        // 2. Akun Owner
        User::create([
            'nama'     => 'Owner Bisnis',
            'email'    => 'owner@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'owner',
            'no_hp'    => '081234567891',
            'status'   => 'aktif',
            'alamat'   => 'Jl. Owner No. 2',
        ]);

        // 3. Akun Developer
        User::create([
            'nama'     => 'Developer System',
            'email'    => 'developer@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'developer',
            'no_hp'    => '081234567892',
            'status'   => 'aktif',
            'alamat'   => 'Jl. Developer No. 3',
        ]);
    }
}