<?php

use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Products\ProductIndex;
use App\Livewire\Categories\CategoryIndex;
use App\Livewire\Transactions\TransactionIndex;
use App\Livewire\Pos\PosIndex;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\StockManagement\StockIndex;
use App\Http\Middleware\IsAdmin;

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
    Route::get('/print-struk/{invoice}', [PosController::class, 'printStruk'])->name('print.struk');
    Route::get('/print-shift/{id}', [PosController::class, 'printShift'])->name('print.shift');
    Route::get('/transactions', TransactionIndex::class)->name('transactions.index');

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
        Route::get('/stock-management', StockIndex::class)->name('stock-management.index');
        Route::get('/hpp-management', \App\Livewire\Hpp\HppIndex::class)->name('hpp.index');
        Route::get('/reports', ReportIndex::class)->name('reports.index');
        Route::get('/shifts', \App\Livewire\Reports\ShiftIndex::class)->name('shifts.index');
        Route::get('/settings', \App\Livewire\Settings\SettingIndex::class)->name('settings.index');
        Route::get('/users', \App\Livewire\Users\UserIndex::class)->name('users.index');
        Route::get('/barcodes', \App\Livewire\Products\BarcodeIndex::class)->name('barcodes.index');
        Route::post('/print-barcodes', [\App\Http\Controllers\BarcodeController::class, 'print'])->name('barcodes.print');
    });
});
