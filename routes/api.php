<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosApiController;

// Public Auth routes
Route::prefix('auth')->group(function () {
    Route::get('/cashiers', [AuthController::class, 'getCashiers']);
    Route::post('/pin-login', [AuthController::class, 'pinLogin']);
});

// Protected POS routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth info & logout
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // POS Data & Operations
    Route::prefix('pos')->group(function () {
        Route::get('/bootstrap', [PosApiController::class, 'bootstrap']);
        Route::get('/addons', [PosApiController::class, 'getAddons']);
        
        // Shift Management
        Route::get('/shift/current', [PosApiController::class, 'currentShift']);
        Route::post('/shift/start', [PosApiController::class, 'startShift']);
        Route::post('/shift/end', [PosApiController::class, 'endShift']);

        // Orders & Transactions
        Route::post('/checkout', [PosApiController::class, 'checkout']);
        Route::get('/transactions/today', [PosApiController::class, 'todayTransactions']);
        Route::get('/transactions/{id}/receipt', [PosApiController::class, 'getReceiptData']);

        // Open Bills / Hold Orders
        Route::get('/open-bills', [PosApiController::class, 'getOpenBills']);
        Route::post('/open-bills', [PosApiController::class, 'saveOpenBill']);
        Route::get('/open-bills/{id}', [PosApiController::class, 'getOpenBillDetail']);
        Route::post('/open-bills/{id}/cancel', [PosApiController::class, 'cancelOpenBill']);

        // Menu & Category Availability (Item 86 / Menu Habis)
        Route::get('/availability', [PosApiController::class, 'getAvailability']);
        Route::post('/products/{id}/toggle-availability', [PosApiController::class, 'toggleProductAvailability']);
        Route::post('/categories/{id}/toggle-availability', [PosApiController::class, 'toggleCategoryAvailability']);

        // Online Orders Management (Pesanan Masuk Self-Order)
        Route::prefix('online-orders')->group(function () {
            Route::get('/', [PosApiController::class, 'getOnlineOrders']);
            Route::get('/check-new', [PosApiController::class, 'checkNewOnlineOrders']);
            Route::get('/stats', [PosApiController::class, 'getOnlineOrdersStats']);
            Route::post('/toggle-active', [PosApiController::class, 'toggleOnlineOrderActive']);
            Route::get('/{id}', [PosApiController::class, 'getOnlineOrderDetail']);
            Route::post('/{id}/status', [PosApiController::class, 'updateOnlineOrderStatus']);
            Route::get('/{id}/receipt', [PosApiController::class, 'getOnlineOrderReceipt']);
            Route::get('/{id}/kitchen', [PosApiController::class, 'getOnlineOrderKitchenSlip']);
        });

        // Offline Batch Sync
        Route::post('/sync-offline', [PosApiController::class, 'syncOffline']);
    });
});
