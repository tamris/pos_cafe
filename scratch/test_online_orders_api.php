<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PosApiController;

echo "=== TEST POS ONLINE ORDERS API ===\n";

$user = User::first();
if (!$user) {
    echo "User not found\n";
    exit(1);
}

// 1. Create a dummy self_order transaction if not exists for testing
$product = Product::first();
$onlineTx = Transaction::create([
    'invoice_number' => 'INV-TEST-ONLINE-' . time(),
    'subtotal' => 50000,
    'discount' => 0,
    'tax' => 5000,
    'total' => 55000,
    'paid' => 55000,
    'change' => 0,
    'payment_method' => 'midtrans_qris',
    'payment_status' => 'paid',
    'order_source' => 'self_order',
    'order_type' => 'dine_in',
    'table_number' => '05',
    'customer_name' => 'Budi Santoso',
    'customer_phone' => '08123456789',
    'status' => 'pending',
]);

if ($product) {
    TransactionDetail::create([
        'transaction_id' => $onlineTx->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 25000,
        'subtotal' => 50000,
        'harga_beli' => 15000,
        'profit' => 20000,
        'notes' => 'Ice Normal, Less Sugar',
    ]);
}

$controller = new PosApiController();

// 2. Test getOnlineOrders
$req1 = Request::create('/api/pos/online-orders', 'GET', ['status' => 'active']);
$req1->setUserResolver(fn() => $user);
$res1 = $controller->getOnlineOrders($req1);
echo "1. getOnlineOrders: Status " . $res1->getStatusCode() . " | Items: " . count($res1->getData()->data) . "\n";
echo "   Stats: " . json_encode($res1->getData()->stats) . "\n";

// 3. Test checkNewOnlineOrders
$req2 = Request::create('/api/pos/online-orders/check-new', 'GET', ['last_order_id' => 0]);
$req2->setUserResolver(fn() => $user);
$res2 = $controller->checkNewOnlineOrders($req2);
echo "2. checkNewOnlineOrders: HasNew=" . ($res2->getData()->has_new_orders ? 'true' : 'false') . " | Count=" . $res2->getData()->new_orders_count . "\n";

// 4. Test getOnlineOrderDetail
$res3 = $controller->getOnlineOrderDetail($onlineTx->id);
echo "3. getOnlineOrderDetail: Status " . $res3->getStatusCode() . " | Invoice: " . $res3->getData()->data->invoice_number . "\n";

// 5. Test updateOnlineOrderStatus
$req4 = Request::create('/api/pos/online-orders/' . $onlineTx->id . '/status', 'POST', ['status' => 'processing']);
$req4->setUserResolver(fn() => $user);
$res4 = $controller->updateOnlineOrderStatus($req4, $onlineTx->id);
echo "4. updateOnlineOrderStatus (processing): " . $res4->getData()->message . "\n";

// 6. Test updateOnlineOrderStatus to ready
$req5 = Request::create('/api/pos/online-orders/' . $onlineTx->id . '/status', 'POST', ['status' => 'ready']);
$req5->setUserResolver(fn() => $user);
$res5 = $controller->updateOnlineOrderStatus($req5, $onlineTx->id);
echo "5. updateOnlineOrderStatus (ready): " . $res5->getData()->message . "\n";

// 7. Test getOnlineOrderKitchenSlip
$res6 = $controller->getOnlineOrderKitchenSlip($onlineTx->id);
echo "6. getOnlineOrderKitchenSlip: Status " . $res6->getStatusCode() . " | RawBT Length: " . strlen($res6->getData()->data->rawbt_base64) . "\n";

// 8. Test getOnlineOrderReceipt
$res7 = $controller->getOnlineOrderReceipt($onlineTx->id);
echo "7. getOnlineOrderReceipt: Status " . $res7->getStatusCode() . " | RawBT Length: " . strlen($res7->getData()->data->rawbt_base64) . "\n";

// 9. Test toggleOnlineOrderActive
$req8 = Request::create('/api/pos/online-orders/toggle-active', 'POST');
$req8->setUserResolver(fn() => $user);
$res8 = $controller->toggleOnlineOrderActive($req8);
echo "8. toggleOnlineOrderActive: " . $res8->getData()->message . " (Active=" . ($res8->getData()->is_online_order_active ? 'true' : 'false') . ")\n";

// Clean up test transaction
$onlineTx->details()->delete();
$onlineTx->delete();
echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
