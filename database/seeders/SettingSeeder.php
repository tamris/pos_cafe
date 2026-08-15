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
            'shop_name' => 'Cafe Noli',
            'address' => 'Jl. Kopi Arabica No. 8, Jakarta Selatan',
            'phone' => '0812-3456-7890',
            'receipt_footer' => "Terima kasih telah berkunjung ke Cafe Noli! ☕\nEnjoy your fresh coffee & food! Follow us @cafenoli.id",
            'wifi_name' => 'CafeNoli_Guest',
            'wifi_password' => 'nolicoffee2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
