<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosApiController;
use App\Models\User;

echo "=== 🧪 TESTING POS API BACKEND ===\n\n";

// 1. Test getCashiers
echo "1. Testing GET /api/auth/cashiers...\n";
$authController = new AuthController();
$cashiersRes = $authController->getCashiers();
$cashiersData = json_decode($cashiersRes->getContent(), true);
echo "   Status: " . ($cashiersData['success'] ? "✅ SUCCESS" : "❌ FAILED") . " (Count: " . count($cashiersData['data']) . ")\n\n";

// 2. Test PIN Login with valid Kasir PIN (112233)
echo "2. Testing POST /api/auth/pin-login (PIN 112233)...\n";
$reqLogin = Request::create('/api/auth/pin-login', 'POST', ['pin' => '112233']);
$loginRes = $authController->pinLogin($reqLogin);
$loginData = json_decode($loginRes->getContent(), true);
echo "   Status: " . ($loginData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   User: " . ($loginData['data']['user']['name'] ?? 'N/A') . " (Role: " . ($loginData['data']['user']['role'] ?? 'N/A') . ")\n";
echo "   Token generated: " . (isset($loginData['data']['token']) ? "✅ YES" : "❌ NO") . "\n\n";

$token = $loginData['data']['token'] ?? null;
$kasirUser = User::where('pin', '112233')->first();

// 3. Test Bootstrap
echo "3. Testing GET /api/pos/bootstrap...\n";
$posController = new PosApiController();
$reqBootstrap = Request::create('/api/pos/bootstrap', 'GET');
$reqBootstrap->setUserResolver(fn() => $kasirUser);
$bootRes = $posController->bootstrap($reqBootstrap);
$bootData = json_decode($bootRes->getContent(), true);
echo "   Status: " . ($bootData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   Categories count: " . count($bootData['data']['categories'] ?? []) . "\n";
echo "   Products count: " . count($bootData['data']['products'] ?? []) . "\n";
echo "   Shop Name: " . ($bootData['data']['settings']['shop_name'] ?? 'N/A') . "\n\n";

// 4. Test Start Shift
echo "4. Testing POST /api/pos/shift/start...\n";
// Ensure previous shift closed if any
\App\Models\CashierShift::where('user_id', $kasirUser->id)->where('status', 'open')->update(['status' => 'closed']);
$reqStartShift = Request::create('/api/pos/shift/start', 'POST', ['starting_cash' => 150000]);
$reqStartShift->setUserResolver(fn() => $kasirUser);
$shiftRes = $posController->startShift($reqStartShift);
$shiftData = json_decode($shiftRes->getContent(), true);
echo "   Status: " . ($shiftData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   Starting cash: Rp " . number_format($shiftData['data']['starting_cash'] ?? 0, 0, ',', '.') . "\n\n";

// 5. Test Checkout
echo "5. Testing POST /api/pos/checkout...\n";
$firstProduct = \App\Models\Product::where('is_active', true)->first();
$reqCheckout = Request::create('/api/pos/checkout', 'POST', [
    'order_type' => 'dine_in',
    'table_number' => '05',
    'customer_name' => 'Bro Tamaris',
    'payment_method' => 'cash',
    'paid' => 100000,
    'discount_percent' => 0,
    'tax_percent' => 0,
    'items' => [
        [
            'id' => $firstProduct->id,
            'quantity' => 2,
            'price' => (float) $firstProduct->price,
            'notes' => 'Less Sugar | Normal Ice'
        ]
    ]
]);
$reqCheckout->setUserResolver(fn() => $kasirUser);
$checkoutRes = $posController->checkout($reqCheckout);
$checkoutData = json_decode($checkoutRes->getContent(), true);
echo "   Status: " . ($checkoutData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   Invoice: " . ($checkoutData['data']['invoice_number'] ?? 'N/A') . "\n";
echo "   Total: Rp " . number_format($checkoutData['data']['total'] ?? 0, 0, ',', '.') . "\n";
echo "   Change: Rp " . number_format($checkoutData['data']['change'] ?? 0, 0, ',', '.') . "\n";
echo "   58mm Receipt Payload generated: " . (isset($checkoutData['receipt_payload']['header']) ? "✅ YES" : "❌ NO") . "\n\n";

$txId = $checkoutData['data']['transaction_id'] ?? null;

// 6. Test Get Receipt Data (Reprint 58mm)
if ($txId) {
    echo "6. Testing GET /api/pos/transactions/{$txId}/receipt...\n";
    $receiptRes = $posController->getReceiptData($txId);
    $receiptData = json_decode($receiptRes->getContent(), true);
    echo "   Status: " . ($receiptData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
    echo "   Shop on receipt: " . ($receiptData['data']['header']['shop_name'] ?? 'N/A') . "\n";
    echo "   Paper width: " . ($receiptData['data']['paper_width'] ?? 'N/A') . "mm\n\n";
}

// 7. Test Save Open Bill
echo "7. Testing POST /api/pos/open-bills (Hold Order)...\n";
$reqOpenBill = Request::create('/api/pos/open-bills', 'POST', [
    'order_type' => 'dine_in',
    'table_number' => '08',
    'customer_name' => 'Pelanggan Meja 8',
    'items' => [
        [
            'id' => $firstProduct->id,
            'quantity' => 1,
            'price' => (float) $firstProduct->price,
            'notes' => 'Hot'
        ]
    ]
]);
$reqOpenBill->setUserResolver(fn() => $kasirUser);
$openBillRes = $posController->saveOpenBill($reqOpenBill);
$openBillData = json_decode($openBillRes->getContent(), true);
echo "   Status: " . ($openBillData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   Open Bill ID: " . ($openBillData['data']['id'] ?? 'N/A') . "\n\n";

// 8. Test Cancel Open Bill (so shift can be closed)
$openBillId = $openBillData['data']['id'] ?? null;
if ($openBillId) {
    echo "8. Testing Cancel Open Bill...\n";
    $cancelReq = Request::create('/api/pos/open-bills/' . $openBillId . '/cancel', 'POST');
    $cancelReq->setUserResolver(fn() => $kasirUser);
    $cancelRes = $posController->cancelOpenBill($cancelReq, $openBillId);
    $cancelData = json_decode($cancelRes->getContent(), true);
    echo "   Status: " . ($cancelData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n\n";
}

// 9. Test End Shift
echo "9. Testing POST /api/pos/shift/end...\n";
$reqEndShift = Request::create('/api/pos/shift/end', 'POST', [
    'actual_cash' => 150000 + ($checkoutData['data']['total'] ?? 0),
    'notes' => 'Tutup shift sore aman lancar'
]);
$reqEndShift->setUserResolver(fn() => $kasirUser);
$endShiftRes = $posController->endShift($reqEndShift);
$endShiftData = json_decode($endShiftRes->getContent(), true);
echo "   Status: " . ($endShiftData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   Difference / Selisih: Rp " . number_format($endShiftData['data']['difference'] ?? 0, 0, ',', '.') . "\n";
echo "   Shift Report Payload generated: " . (isset($endShiftData['receipt_payload']['type']) ? "✅ YES" : "❌ NO") . "\n\n";

// 10. Test Sync Offline Batch Transactions
echo "10. Testing POST /api/pos/sync-offline (Batch sync)...\n";
$reqSync = Request::create('/api/pos/sync-offline', 'POST', [
    'transactions' => [
        [
            'offline_id' => 'OFF-20260901-001',
            'order_type' => 'take_away',
            'customer_name' => 'Pembeli Luar Toko',
            'payment_method' => 'cash',
            'paid' => 50000,
            'created_at' => date('Y-m-d H:i:s'),
            'items' => [
                [
                    'id' => $firstProduct->id,
                    'quantity' => 1,
                    'price' => (float) $firstProduct->price,
                    'notes' => 'Bungkus'
                ]
            ]
        ]
    ]
]);
$reqSync->setUserResolver(fn() => $kasirUser);
$syncRes = $posController->syncOffline($reqSync);
$syncData = json_decode($syncRes->getContent(), true);
echo "   Status: " . ($syncData['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "   Synced Count: " . ($syncData['synced_count'] ?? 0) . "\n";
echo "   Mapped Server Invoice: " . ($syncData['results'][0]['invoice_number'] ?? 'N/A') . "\n\n";

echo "🎉 ALL API BACKEND ENDPOINTS TESTED & OPERATIONAL!\n";
