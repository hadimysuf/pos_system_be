<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'PT Sumber Makmur',
                'contact' => '081234567890',
                'email' => 'sumbermakmur@gmail.com',
                'address' => 'Jl. Raya Industri No. 12, Bandung',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'CV Sejahtera Abadi',
                'contact' => '089912345678',
                'email' => 'sejahtera.abadi@mail.com',
                'address' => 'Jl. Merdeka No. 15, Jakarta',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Toko Grosir Jaya',
                'contact' => '085212345678',
                'email' => 'grosirjaya@gmail.com',
                'address' => 'Jl. Pasar Lama No. 5, Bogor',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
