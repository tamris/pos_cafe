<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            [
                'id' => 1,
                'name' => '☕ Espresso & Coffee',
                'description' => 'Kopi racikan barista khas Cafe Noli (Hot & Iced)'
            ],
            [
                'id' => 2,
                'name' => '🍵 Non-Coffee & Tea',
                'description' => 'Minuman non-kopi segar, matcha, cokelat, dan artisan tea'
            ],
            [
                'id' => 3,
                'name' => '🥐 Pastry & Bakery',
                'description' => 'Croissant, roti panggang, dan dessert manis pendamping kopi'
            ],
            [
                'id' => 4,
                'name' => '🍝 Main Course & Food',
                'description' => 'Makanan berat khas Cafe Noli (Rice Bowl, Pasta, Nasi Goreng)'
            ],
            [
                'id' => 5,
                'name' => '🍟 Snacks & Appetizers',
                'description' => 'Camilan gurih dan finger food untuk bersantai'
            ]
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'id' => $category['id'],
                'name' => $category['name'],
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}