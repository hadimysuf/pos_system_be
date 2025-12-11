<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'role_id' => 1, // Admin
                'name' => 'Admin Utama',
                'email' => 'admin@pos.local',
                'password' => Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 2, // Kasir
                'name' => 'Kasir 1',
                'email' => 'kasir@pos.local',
                'password' => Hash::make('kasir123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 3, // Gudang
                'name' => 'Petugas Gudang',
                'email' => 'gudang@pos.local',
                'password' => Hash::make('gudang123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
