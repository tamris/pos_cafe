<?php

use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Products\ProductIndex;
use App\Livewire\Categories\CategoryIndex;
use App\Livewire\Addons\AddonIndex;
use App\Livewire\Transactions\TransactionIndex;
use App\Livewire\Pos\PosIndex;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\StockManagement\StockIndex;
use App\Livewire\OnlineOrders\OnlineOrderIndex;
use App\Livewire\Customer\CustomerOrder;
use App\Livewire\Customer\CustomerPayment;
use App\Livewire\Customer\CustomerStatus;
use App\Http\Middleware\IsAdmin;

// Customer Self-Order Routes (Public)
Route::get('/order', CustomerOrder::class)->name('customer.order');
Route::get('/order/pay/{token}', CustomerPayment::class)->name('customer.payment');
Route::get('/order/status/{token}', CustomerStatus::class)->name('customer.status');

// Midtrans Webhook Notification
Route::post('/api/midtrans/notification', [\App\Http\Controllers\MidtransCallbackController::class, 'handle'])->name('midtrans.notification');
Route::post('/midtrans/callback', [\App\Http\Controllers\MidtransCallbackController::class, 'handle']);

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('login');
});

// Test route untuk debug - hapus setelah berhasil
Route::get('/test-gemini-debug', function () {
    $apiKey = env('GEMINI_API_KEY');

    echo "<h2>Debug Gemini API</h2>";
    echo "API Key: " . (empty($apiKey) ? 'MISSING' : 'EXISTS') . "<br>";
    echo "Key (first 10): " . substr($apiKey, 0, 10) . "...<br><br>";

    try {
        $response = Http::timeout(30)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Halo, balas dengan "TEST BERHASIL" dalam bahasa Indonesia']
                        ]
                    ]
                ]
            ]);

        echo "Status: " . $response->status() . "<br>";

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                echo "<strong>SUCCESS:</strong> " . $data['candidates'][0]['content']['parts'][0]['text'] . "<br>";
            } else {
                echo "<strong>ERROR - No text in response:</strong><pre>";
                print_r($data);
                echo "</pre>";
            }
        } else {
            echo "<strong>ERROR:</strong> " . $response->body() . "<br>";
        }
    } catch (\Exception $e) {
        echo "<strong>EXCEPTION:</strong> " . $e->getMessage() . "<br>";
    }

    echo "<br><h3>Test dengan Service Class</h3>";
    try {
        $service = app(App\Services\GeminiAIService::class);
        $result = $service->generateResponse('Test dari service class');
        echo "<strong>Service Result:</strong> " . $result . "<br>";
    } catch (\Exception $e) {
        echo "<strong>Service Exception:</strong> " . $e->getMessage() . "<br>";
    }
});

// Test route - hapus setelah berhasil
Route::get('/test-gemini', function () {
    $service = app(App\Services\GeminiAIService::class);
    $response = $service->generateResponse('Halo, apa kabar?, apakah ada penjualan hari ini');
    dd($response);
});


// Auth routes
Route::middleware('auth')->group(function () {
    Route::get('/pos', PosIndex::class)->name('pos.index');
    Route::get('/online-orders', OnlineOrderIndex::class)->name('online-orders.index');
    Route::get('/print-struk/{invoice}', [PosController::class, 'printStruk'])->name('print.struk');
    Route::get('/rawbt-struk/{invoice}', [PosController::class, 'rawbtStruk'])->name('rawbt.struk');
    Route::get('/print-kitchen/{invoice}', [PosController::class, 'printKitchen'])->name('print.kitchen');
    Route::get('/rawbt-kitchen/{invoice}', [PosController::class, 'rawbtKitchen'])->name('rawbt.kitchen');
    Route::get('/print-shift/{id}', [PosController::class, 'printShift'])->name('print.shift');
    Route::get('/rawbt-shift/{id}', [PosController::class, 'rawbtShift'])->name('rawbt.shift');
    Route::get('/transactions', TransactionIndex::class)->name('transactions.index');
    Route::get('/shifts', \App\Livewire\Reports\ShiftIndex::class)->name('shifts.index');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/')->with('success', 'Anda telah berhasil keluar dari sistem POS.');
    })->name('logout');

    Route::middleware(IsAdmin::class)->group(function () {

        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/products', ProductIndex::class)->name('products.index');
        Route::get('/categories', CategoryIndex::class)->name('categories.index');
        Route::get('/addons', AddonIndex::class)->name('addons.index');
        Route::get('/stock-management', StockIndex::class)->name('stock-management.index');
        Route::get('/hpp-management', \App\Livewire\Hpp\HppIndex::class)->name('hpp.index');
        Route::get('/reports', ReportIndex::class)->name('reports.index');
        Route::get('/settings', \App\Livewire\Settings\SettingIndex::class)->name('settings.index');
        Route::get('/users', \App\Livewire\Users\UserIndex::class)->name('users.index');
        Route::get('/barcodes', \App\Livewire\Products\BarcodeIndex::class)->name('barcodes.index');
        Route::post('/print-barcodes', [\App\Http\Controllers\BarcodeController::class, 'print'])->name('barcodes.print');
    });
});
