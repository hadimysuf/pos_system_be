<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kemeja',
                'description' => 'Produk kategori kemeja pria dan wanita',
            ],
            [
                'name' => 'Celana',
                'description' => 'Produk kategori celana jeans dan kain',
            ],
            [
                'name' => 'Jaket',
                'description' => 'Produk kategori jaket dan hoodie',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
