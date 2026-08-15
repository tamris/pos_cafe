<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $products = [
            // ☕ Espresso & Coffee (Category 1)
            [
                'category_id' => 1,
                'name' => 'Noli Signature Palm Sugar Latte',
                'sku' => 'COF001',
                'barcode' => '8991001',
                'description' => 'Kopi gula aren khas Cafe Noli dengan espresso ganda dan susu creamy',
                'price' => 22000,
                'harga_beli' => 9000,
                'stock' => 100,
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Caffè Latte',
                'sku' => 'COF002',
                'barcode' => '8991002',
                'description' => 'Espresso lembut dipadu steamed milk segar',
                'price' => 24000,
                'harga_beli' => 10000,
                'stock' => 80,
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Americano (Hot / Iced)',
                'sku' => 'COF003',
                'barcode' => '8991003',
                'description' => 'Double shot espresso murni dengan air mineral',
                'price' => 18000,
                'harga_beli' => 6000,
                'stock' => 120,
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Caramel Macchiato',
                'sku' => 'COF004',
                'barcode' => '8991004',
                'description' => 'Espresso dengan sirup vanila dan drizzle saus karamel gurih',
                'price' => 28000,
                'harga_beli' => 12000,
                'stock' => 60,
                'image' => null,
            ],
            [
                'category_id' => 1,
                'name' => 'Manual Brew V60 (Arabica Gayo)',
                'sku' => 'COF005',
                'barcode' => '8991005',
                'description' => 'Seduh manual biji kopi Arabica Aceh Gayo bersensasi fruity',
                'price' => 26000,
                'harga_beli' => 11000,
                'stock' => 50,
                'image' => null,
            ],

            // 🍵 Non-Coffee & Tea (Category 2)
            [
                'category_id' => 2,
                'name' => 'Matcha Cream Latte',
                'sku' => 'NCF001',
                'barcode' => '8992001',
                'description' => 'Matcha Uji Jepang premium dengan susu manis lembut',
                'price' => 25000,
                'harga_beli' => 11000,
                'stock' => 70,
                'image' => null,
            ],
            [
                'category_id' => 2,
                'name' => 'Dark Chocolate Velvet',
                'sku' => 'NCF002',
                'barcode' => '8992002',
                'description' => 'Cokelat pekat rich dan creamy khas Noli Cafe',
                'price' => 24000,
                'harga_beli' => 10000,
                'stock' => 65,
                'image' => null,
            ],
            [
                'category_id' => 2,
                'name' => 'Earl Grey Milk Tea',
                'sku' => 'NCF003',
                'barcode' => '8992003',
                'description' => 'Teh Earl Grey beraroma bergamot dengan susu segar',
                'price' => 20000,
                'harga_beli' => 8000,
                'stock' => 90,
                'image' => null,
            ],
            [
                'category_id' => 2,
                'name' => 'Lychee Lemon Sparkler',
                'sku' => 'NCF004',
                'barcode' => '8992004',
                'description' => 'Mocktail soda leci dan perasan lemon segar',
                'price' => 22000,
                'harga_beli' => 8500,
                'stock' => 75,
                'image' => null,
            ],

            // 🥐 Pastry & Bakery (Category 3)
            [
                'category_id' => 3,
                'name' => 'Butter Croissant Classic',
                'sku' => 'PST001',
                'barcode' => '8993001',
                'description' => 'Croissant berlapis dengan mentega Prancis yang garing',
                'price' => 20000,
                'harga_beli' => 8000,
                'stock' => 40,
                'image' => null,
            ],
            [
                'category_id' => 3,
                'name' => 'Almond Croissant',
                'sku' => 'PST002',
                'barcode' => '8993002',
                'description' => 'Croissant berisian krim almond dan taburan almond slice',
                'price' => 25000,
                'harga_beli' => 11000,
                'stock' => 35,
                'image' => null,
            ],
            [
                'category_id' => 3,
                'name' => 'Fudge Brownies Slice',
                'sku' => 'PST003',
                'barcode' => '8993003',
                'description' => 'Kue brownies cokelat legit dengan toping choco chip',
                'price' => 18000,
                'harga_beli' => 7000,
                'stock' => 45,
                'image' => null,
            ],

            // 🍝 Main Course & Food (Category 4)
            [
                'category_id' => 4,
                'name' => 'Nasi Goreng Noli Special',
                'sku' => 'FOD001',
                'barcode' => '8994001',
                'description' => 'Nasi goreng bumbu rempah dengan telur mata sapi dan ayam pop',
                'price' => 32000,
                'harga_beli' => 14000,
                'stock' => 50,
                'image' => null,
            ],
            [
                'category_id' => 4,
                'name' => 'Spaghetti Carbonara Creamy',
                'sku' => 'FOD002',
                'barcode' => '8994002',
                'description' => 'Spaghetti saus krim keju parmesan dengan smoked beef',
                'price' => 35000,
                'harga_beli' => 15000,
                'stock' => 40,
                'image' => null,
            ],
            [
                'category_id' => 4,
                'name' => 'Chicken Katsu Don Rice Bowl',
                'sku' => 'FOD003',
                'barcode' => '8994003',
                'description' => 'Nasi hangat dengan katsu ayam renyah dan saus donburi manis',
                'price' => 34000,
                'harga_beli' => 14500,
                'stock' => 45,
                'image' => null,
            ],

            // 🍟 Snacks & Appetizers (Category 5)
            [
                'category_id' => 5,
                'name' => 'Truffle Parmesan Fries',
                'sku' => 'SNK001',
                'barcode' => '8995001',
                'description' => 'Kentang goreng aroma truffle dengan parutan keju parmesan',
                'price' => 22000,
                'harga_beli' => 8500,
                'stock' => 60,
                'image' => null,
            ],
            [
                'category_id' => 5,
                'name' => 'Crispy Chicken Wings (Honey Garlic)',
                'sku' => 'SNK002',
                'barcode' => '8995002',
                'description' => 'Sayap ayam goreng renyah dengan baluran saus madu gurih (5 pcs)',
                'price' => 28000,
                'harga_beli' => 12000,
                'stock' => 50,
                'image' => null,
            ]
        ];

        foreach ($products as $product) {
            DB::table('products')->insert([
                'category_id' => $product['category_id'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'barcode' => $product['barcode'],
                'description' => $product['description'],
                'price' => $product['price'],
                'harga_beli' => $product['harga_beli'],
                'stock' => $product['stock'],
                'image' => $product['image'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}