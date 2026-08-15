<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->truncate();

        DB::table('settings')->insert([
            'shop_name' => 'Cafe & Eatery',
            'address' => 'Jl. Kopi Arabica No. 8, Jakarta Selatan',
            'phone' => '0812-3456-7890',
            'receipt_footer' => "Terima kasih telah berkunjung! ☕\nEnjoy your fresh coffee & delicious food!",
            'wifi_name' => 'Cafe_Guest',
            'wifi_password' => 'cafepos2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
