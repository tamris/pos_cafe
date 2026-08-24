<?php

require 'd:/Antigravity/pos-inventory/vendor/autoload.php';

$app = require_once 'd:/Antigravity/pos-inventory/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "No admin found!" . PHP_EOL;
    exit(1);
}

Auth::login($admin);
echo "Logged in as Admin: " . $admin->name . " (" . $admin->email . ")" . PHP_EOL;

$components = [
    'Dashboard' => \App\Livewire\Dashboard::class,
    'ProductIndex' => \App\Livewire\Products\ProductIndex::class,
    'CategoryIndex' => \App\Livewire\Categories\CategoryIndex::class,
    'StockIndex' => \App\Livewire\StockManagement\StockIndex::class,
    'HppIndex' => \App\Livewire\Hpp\HppIndex::class,
    'ReportIndex' => \App\Livewire\Reports\ReportIndex::class,
    'ShiftIndex' => \App\Livewire\Reports\ShiftIndex::class,
    'SettingIndex' => \App\Livewire\Settings\SettingIndex::class,
    'UserIndex' => \App\Livewire\Users\UserIndex::class,
    'BarcodeIndex' => \App\Livewire\Products\BarcodeIndex::class,
    'PosIndex' => \App\Livewire\Pos\PosIndex::class,
    'OnlineOrderIndex' => \App\Livewire\OnlineOrders\OnlineOrderIndex::class,
    'TransactionIndex' => \App\Livewire\Transactions\TransactionIndex::class,
];

foreach ($components as $name => $class) {
    try {
        \Livewire\Livewire::test($class)->assertStatus(200);
        echo "✅ [SUCCESS] $name mounted and rendered without errors." . PHP_EOL;
    } catch (\Throwable $e) {
        echo "❌ [ERROR] $name failed: " . $e->getMessage() . " on line " . $e->getLine() . " of " . $e->getFile() . PHP_EOL;
    }
}
