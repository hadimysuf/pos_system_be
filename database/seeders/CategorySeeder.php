<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->insert([
            ['name' => 'Kemeja', 'description' => 'Produk kategori kemeja pria dan wanita', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Celana', 'description' => 'Produk kategori celana jeans dan kain', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jaket', 'description' => 'Produk kategori jaket dan hoodie', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
