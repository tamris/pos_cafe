<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class MidtransService
{
    public function __construct()
    {
        self::init();
    }

    /**
     * Inisialisasi konfigurasi SDK Midtrans
     */
    public static function init(): void
    {
        Config::$serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        Config::$clientKey = config('midtrans.client_key', env('MIDTRANS_CLIENT_KEY', ''));
        Config::$isProduction = (bool) config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        Config::$isSanitized = (bool) config('midtrans.is_sanitized', env('MIDTRANS_IS_SANITIZED', true));
        Config::$is3ds = (bool) config('midtrans.is_3ds', env('MIDTRANS_IS_3DS', true));
    }

    /**
     * Dapatkan Snap Token untuk pembayaran
     */
    public function getSnapToken(Transaction $transaction): ?string
    {
        self::init();

        // Jika sudah lunas, tidak perlu token lagi
        if ($transaction->payment_status === 'paid') {
            return null;
        }

        // Jika sudah ada snap_token yang tersimpan, gunakan kembali
        if (!empty($transaction->snap_token)) {
            return $transaction->snap_token;
        }

        $grossAmount = (int) round($transaction->total);

        // Susun item details
        $itemDetails = [];
        $calculatedSum = 0;

        foreach ($transaction->details as $detail) {
            $price = (int) round($detail->price);
            $qty = (int) $detail->quantity;
            $calculatedSum += ($price * $qty);

            $itemDetails[] = [
                'id' => (string) $detail->product_id,
                'price' => $price,
                'quantity' => $qty,
                'name' => mb_substr($detail->product?->name ?? 'Menu Item', 0, 50),
            ];
        }

        // Diskon jika ada
        if ((float) $transaction->discount > 0) {
            $discountAmount = (int) round($transaction->discount);
            $calculatedSum -= $discountAmount;
            $itemDetails[] = [
                'id' => 'DISC',
                'price' => -$discountAmount,
                'quantity' => 1,
                'name' => 'Diskon Promo',
            ];
        }

        // Pajak jika ada
        if ((float) $transaction->tax > 0) {
            $taxAmount = (int) round($transaction->tax);
            $calculatedSum += $taxAmount;
            $itemDetails[] = [
                'id' => 'TAX',
                'price' => $taxAmount,
                'quantity' => 1,
                'name' => 'Pajak PB1',
            ];
        }

        // Pastikan sum(item_details) sama persis dengan gross_amount (Aturan ketat Midtrans)
        if ($calculatedSum !== $grossAmount) {
            // Jika ada selisih pembulatan, sesuaikan dengan item penyesuaian
            $diff = $grossAmount - $calculatedSum;
            $itemDetails[] = [
                'id' => 'ADJ',
                'price' => $diff,
                'quantity' => 1,
                'name' => 'Penyesuaian Total',
            ];
        }

        // Customer details
        $customerDetails = [
            'first_name' => $transaction->customer_name ?: 'Pelanggan Cafe',
            'phone' => $transaction->customer_phone ?: '08123456789',
        ];

        // Payload Snap
        $params = [
            'transaction_details' => [
                'order_id' => $transaction->invoice_number,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('customer.status', ['token' => $transaction->order_token]),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan snap token ke transaksi
            $transaction->update([
                'snap_token' => $snapToken,
            ]);

            return $snapToken;
        } catch (\Throwable $e) {
            Log::error('Midtrans getSnapToken Error: ' . $e->getMessage(), [
                'invoice' => $transaction->invoice_number,
                'params' => $params,
            ]);

            return null;
        }
    }

    /**
     * Cek status transaksi langsung dari Server Midtrans (Direct API Status Check)
     */
    public function checkTransactionStatus(string $orderId): ?array
    {
        self::init();

        try {
            $statusObj = MidtransTransaction::status($orderId);
            $statusData = (array) $statusObj;

            $transactionStatus = $statusData['transaction_status'] ?? null;
            $fraudStatus = $statusData['fraud_status'] ?? null;
            $paymentType = $statusData['payment_type'] ?? 'midtrans';

            $transaction = Transaction::where('invoice_number', $orderId)->first();

            if ($transaction) {
                if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                    if ($transaction->payment_status !== 'paid') {
                        $transaction->update([
                            'payment_status' => 'paid',
                            'payment_method' => $paymentType,
                            'paid' => (float) $transaction->total,
                            'change' => 0,
                            'status' => in_array($transaction->status, ['pending', 'unpaid']) ? 'processing' : $transaction->status,
                        ]);
                    }
                } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    if ($transaction->payment_status !== 'paid') {
                        $transaction->update([
                            'payment_status' => 'failed',
                        ]);
                    }
                }
            }

            return $statusData;
        } catch (\Throwable $e) {
            Log::warning("Midtrans checkTransactionStatus [{$orderId}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle Webhook / Notifikasi HTTP dari Midtrans
     */
    public function handleNotification(array $payload): array
    {
        self::init();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'midtrans';

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return ['status' => 'error', 'message' => 'Invalid notification payload data'];
        }

        // Verifikasi Signature SHA512
        $serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning("Midtrans Webhook: Invalid signature for Order {$orderId}");
            return ['status' => 'error', 'message' => 'Invalid signature key'];
        }

        $transaction = Transaction::where('invoice_number', $orderId)->first();
        if (!$transaction) {
            return ['status' => 'error', 'message' => 'Transaction not found'];
        }

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $transaction->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentType,
                'paid' => (float) $grossAmount,
                'change' => 0,
                'status' => in_array($transaction->status, ['pending', 'unpaid']) ? 'processing' : $transaction->status,
            ]);

            Log::info("Midtrans Webhook: Order {$orderId} marked as PAID via {$paymentType}");
        } elseif ($transactionStatus === 'pending') {
            $transaction->update([
                'payment_status' => 'unpaid',
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $transaction->update([
                'payment_status' => 'failed',
            ]);
            Log::info("Midtrans Webhook: Order {$orderId} status changed to {$transactionStatus}");
        }

        return ['status' => 'success', 'order_id' => $orderId, 'transaction_status' => $transactionStatus];
    }
}
