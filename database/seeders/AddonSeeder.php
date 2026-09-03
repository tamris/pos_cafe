<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Addon;
use App\Models\Category;

class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $coffeeCategory = Category::where('name', 'like', '%Coffee%')->first();
        $teaCategory = Category::where('name', 'like', '%Tea%')->first();
        $foodCategory = Category::where('name', 'like', '%Food%')->orWhere('name', 'like', '%Course%')->first();
        $snackCategory = Category::where('name', 'like', '%Snack%')->first();

        $addons = [
            [
                'name' => 'Extra Shot Espresso',
                'price' => 4000,
                'harga_beli' => 1500,
                'is_active' => true,
                'categories' => array_filter([$coffeeCategory?->id]),
            ],
            [
                'name' => 'Oat Milk Sub',
                'price' => 8000,
                'harga_beli' => 4000,
                'is_active' => true,
                'categories' => array_filter([$coffeeCategory?->id, $teaCategory?->id]),
            ],
            [
                'name' => 'Caramel Syrup',
                'price' => 5000,
                'harga_beli' => 1800,
                'is_active' => true,
                'categories' => array_filter([$coffeeCategory?->id, $teaCategory?->id]),
            ],
            [
                'name' => 'Vanilla Syrup',
                'price' => 5000,
                'harga_beli' => 1800,
                'is_active' => true,
                'categories' => array_filter([$coffeeCategory?->id, $teaCategory?->id]),
            ],
            [
                'name' => 'Whipped Cream',
                'price' => 4000,
                'harga_beli' => 1500,
                'is_active' => true,
                'categories' => array_filter([$coffeeCategory?->id, $teaCategory?->id]),
            ],
            [
                'name' => 'Extra Telur (Sunny Side Up)',
                'price' => 4000,
                'harga_beli' => 2000,
                'is_active' => true,
                'categories' => array_filter([$foodCategory?->id]),
            ],
            [
                'name' => 'Extra Keju Mozzarella',
                'price' => 5000,
                'harga_beli' => 2500,
                'is_active' => true,
                'categories' => array_filter([$foodCategory?->id, $snackCategory?->id]),
            ],
        ];

        foreach ($addons as $item) {
            $catIds = $item['categories'];
            unset($item['categories']);

            $addon = Addon::firstOrCreate(
                ['name' => $item['name']],
                $item
            );

            if (!empty($catIds)) {
                $addon->categories()->syncWithoutDetaching($catIds);
            }
        }
    }
}
