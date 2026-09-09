<?php

namespace App\Services;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\CashierShift;
use App\Models\CashMovement;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Ambil pengaturan aktif dari database.
     */
    public function getSetting(): ?Setting
    {
        return Setting::first();
    }

    /**
     * Dapatkan Bot Token aktif (Database atau .env).
     */
    public function getBotToken(?Setting $setting = null): ?string
    {
        $setting = $setting ?? $this->getSetting();
        $token = $setting?->telegram_bot_token ?: config('services.telegram.bot_token');
        return !empty($token) ? trim($token) : null;
    }

    /**
     * Dapatkan Chat ID aktif (Database atau .env).
     */
    public function getChatId(?Setting $setting = null): ?string
    {
        $setting = $setting ?? $this->getSetting();
        $chatId = $setting?->telegram_chat_id ?: config('services.telegram.chat_id');
        return !empty($chatId) ? trim($chatId) : null;
    }

    /**
     * Cek status switch notifikasi transaksi.
     */
    public function isNotifyTrxEnabled(?Setting $setting = null): bool
    {
        $setting = $setting ?? $this->getSetting();
        if ($setting && !empty($setting->telegram_bot_token) && !is_null($setting->telegram_notify_trx)) {
            return (bool) $setting->telegram_notify_trx;
        }
        return (bool) config('services.telegram.notify_trx', true);
    }

    /**
     * Cek status switch notifikasi tutup shift.
     */
    public function isNotifyShiftEnabled(?Setting $setting = null): bool
    {
        $setting = $setting ?? $this->getSetting();
        if ($setting && !empty($setting->telegram_bot_token) && !is_null($setting->telegram_notify_shift)) {
            return (bool) $setting->telegram_notify_shift;
        }
        return (bool) config('services.telegram.notify_shift', true);
    }

    /**
     * Cek status switch notifikasi void.
     */
    public function isNotifyVoidEnabled(?Setting $setting = null): bool
    {
        $setting = $setting ?? $this->getSetting();
        if ($setting && !empty($setting->telegram_bot_token) && !is_null($setting->telegram_notify_void)) {
            return (bool) $setting->telegram_notify_void;
        }
        return (bool) config('services.telegram.notify_void', true);
    }

    /**
     * Cek apakah bot Telegram sudah dikonfigurasi lengkap.
     */
    public function isConfigured(?Setting $setting = null): bool
    {
        return !empty($this->getBotToken($setting)) && !empty($this->getChatId($setting));
    }

    /**
     * Kirim pesan teks langsung ke Telegram Bot API (Synchronous).
     *
     * @param string $message Pesan dalam format HTML
     * @param string|null $token Bot token override (opsional)
     * @param string|null $chatId Chat ID override (opsional)
     * @return array ['success' => bool, 'message' => string, 'data' => ?array]
     */
    public function sendMessage(string $message, ?string $token = null, ?string $chatId = null): array
    {
        $setting = $this->getSetting();
        $botToken = trim($token ?: ($this->getBotToken($setting) ?? ''));
        $targetChatId = trim($chatId ?: ($this->getChatId($setting) ?? ''));

        if (empty($botToken)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token belum diatur (isi di .env atau pengaturan database).',
                'data' => null,
            ];
        }

        if (empty($targetChatId)) {
            return [
                'success' => false,
                'message' => 'Telegram Chat ID belum diatur (isi di .env atau pengaturan database).',
                'data' => null,
            ];
        }

        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $response = Http::withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])->timeout(10)->post($url, [
                'chat_id' => $targetChatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['ok'] ?? false)) {
                return [
                    'success' => true,
                    'message' => 'Pesan Telegram berhasil terkirim.',
                    'data' => $body['result'] ?? null,
                    'status_code' => $response->status(),
                ];
            }

            $errorDesc = $body['description'] ?? ('HTTP Error ' . $response->status());
            $retryAfter = $body['parameters']['retry_after'] ?? null;

            Log::warning("Telegram API Error [{$response->status()}]: {$errorDesc}", [
                'chat_id' => $targetChatId,
                'retry_after' => $retryAfter,
            ]);

            return [
                'success' => false,
                'message' => 'Telegram API Error: ' . $errorDesc,
                'data' => $body,
                'status_code' => $response->status(),
                'retry_after' => $retryAfter,
            ];
        } catch (\Throwable $e) {
            Log::error('Telegram Service Exception: ' . $e->getMessage(), [
                'chat_id' => $targetChatId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Telegram: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0,
                'retry_after' => null,
            ];
        }
    }

    /**
     * Trigger notifikasi transaksi baru (Asynchronous / Queue).
     */
    public function sendTransactionNotification(Transaction $transaction): void
    {
        try {
            $setting = $this->getSetting();

            if (!$this->isConfigured($setting) || !$this->isNotifyTrxEnabled($setting)) {
                return;
            }

            // Pastikan relasi sudah termuat
            $transaction->loadMissing(['details.product.category', 'user', 'shift']);

            $message = $this->formatTransactionMessage($transaction, $setting);

            SendTelegramNotificationJob::dispatch(
                $message,
                $this->getBotToken($setting),
                $this->getChatId($setting)
            );
        } catch (\Throwable $e) {
            Log::error('Error triggering Telegram transaction notification: ' . $e->getMessage());
        }
    }

    /**
     * Trigger notifikasi tutup shift (Asynchronous / Queue).
     */
    public function sendShiftClosingNotification(CashierShift $shift): void
    {
        try {
            $setting = $this->getSetting();

            if (!$this->isConfigured($setting) || !$this->isNotifyShiftEnabled($setting)) {
                return;
            }

            $shift->loadMissing(['user', 'transactions.details.product.category', 'cashMovements.category']);

            $message = $this->formatShiftClosingMessage($shift, $setting);

            SendTelegramNotificationJob::dispatch(
                $message,
                $this->getBotToken($setting),
                $this->getChatId($setting)
            );
        } catch (\Throwable $e) {
            Log::error('Error triggering Telegram shift notification: ' . $e->getMessage());
        }
    }

    /**
     * Trigger notifikasi pembatalan / void transaksi (Asynchronous / Queue).
     */
    public function sendVoidNotification(Transaction $transaction): void
    {
        try {
            $setting = $this->getSetting();

            if (!$this->isConfigured($setting) || !$this->isNotifyVoidEnabled($setting)) {
                return;
            }

            $transaction->loadMissing(['user', 'cancelledBy', 'details.product.category']);

            $message = $this->formatVoidMessage($transaction, $setting);

            SendTelegramNotificationJob::dispatch(
                $message,
                $this->getBotToken($setting),
                $this->getChatId($setting)
            );
        } catch (\Throwable $e) {
            Log::error('Error triggering Telegram void notification: ' . $e->getMessage());
        }
    }

    /**
     * Trigger notifikasi pengeluaran / arus kas baru (Asynchronous / Queue).
     */
    public function sendCashMovementNotification(CashMovement $movement): void
    {
        try {
            $setting = $this->getSetting();

            if (!$this->isConfigured($setting)) {
                return;
            }

            $movement->loadMissing(['user', 'shift']);
            $message = $this->formatCashMovementMessage($movement, $setting);

            SendTelegramNotificationJob::dispatch(
                $message,
                $this->getBotToken($setting),
                $this->getChatId($setting)
            );
        } catch (\Throwable $e) {
            Log::error('Error triggering Telegram cash movement notification: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi uji coba (Synchronous).
     */
    public function testNotification(?string $token = null, ?string $chatId = null): array
    {
        $setting = $this->getSetting();
        $message = $this->formatTestMessage($setting);

        return $this->sendMessage($message, $token, $chatId);
    }

    /**
     * Tentukan satuan produk (cup vs porsi) secara cerdas berdasarkan nama produk dan nama kategori.
     */
    public function determineUnit(?string $productName, ?string $categoryName): string
    {
        $combined = strtolower(trim(($productName ?? '') . ' ' . ($categoryName ?? '')));

        // Minuman / Beverages -> cup
        if (preg_match('/\b(coffee|kopi|espresso|latte|cappuccino|americano|tea|teh|matcha|chocolate|cokelat|taro|drink|minuman|beverage|juice|jus|boba|frappe|mocktail|smoothie|syrup|ice|es|soda|cup)\b/i', $combined)) {
            return 'cup';
        }

        // Makanan / Meals / Snacks / Pastry -> porsi
        if (preg_match('/\b(food|makanan|meal|snack|cemilan|dish|rice|nasi|noodle|mie|pasta|spaghetti|toast|roti|croissant|pastry|cake|dessert|bowl|porsi|pack)\b/i', $combined)) {
            return 'porsi';
        }

        // Cek kategori jika ada indikasi minuman
        $catLower = strtolower($categoryName ?? '');
        if (str_contains($catLower, 'coffee') || str_contains($catLower, 'kopi') || str_contains($catLower, 'tea') || str_contains($catLower, 'drink') || str_contains($catLower, 'beverage')) {
            return 'cup';
        }

        return 'porsi';
    }

    /**
     * Dapatkan ikon emoji yang representatif untuk kategori menu.
     */
    public function getCategoryIcon(?string $categoryName): string
    {
        $lower = strtolower($categoryName ?? '');
        if (str_contains($lower, 'non-coffee') || str_contains($lower, 'non coffee') || str_contains($lower, 'tea') || str_contains($lower, 'teh') || str_contains($lower, 'matcha') || str_contains($lower, 'juice') || str_contains($lower, 'jus') || str_contains($lower, 'boba')) {
            return '🍵';
        }
        if (str_contains($lower, 'coffee') || str_contains($lower, 'kopi') || str_contains($lower, 'espresso')) {
            return '☕';
        }
        if (str_contains($lower, 'food') || str_contains($lower, 'makanan') || str_contains($lower, 'rice') || str_contains($lower, 'nasi') || str_contains($lower, 'course') || str_contains($lower, 'dish') || str_contains($lower, 'meal')) {
            return '🍽️';
        }
        if (str_contains($lower, 'snack') || str_contains($lower, 'cemilan') || str_contains($lower, 'pastry') || str_contains($lower, 'bakery') || str_contains($lower, 'roti') || str_contains($lower, 'dessert') || str_contains($lower, 'cake')) {
            return '🥐';
        }
        if (str_contains($lower, 'drink') || str_contains($lower, 'beverage') || str_contains($lower, 'minuman')) {
            return '🥤';
        }
        return '🏷️';
    }

    /**
     * Format pesan untuk transaksi baru.
     */
    public function formatTransactionMessage(Transaction $transaction, ?Setting $setting = null): string
    {
        $shopName = htmlspecialchars($setting?->shop_name ?? 'POS Cafe');
        $invoice = htmlspecialchars($transaction->invoice_number);
        $cashier = htmlspecialchars($transaction->user?->name ?? 'Kasir');
        $time = Carbon::parse($transaction->created_at)->translatedFormat('d M Y, H:i') . ' WIB';

        // Format order type (dine_in -> Dine In, takeaway -> Take Away)
        $orderTypeRaw = str_replace('_', ' ', strtolower($transaction->order_type ?? 'dine in'));
        $orderType = ucwords($orderTypeRaw);
        if (!empty($transaction->table_number)) {
            $orderType .= ' (Meja ' . htmlspecialchars($transaction->table_number) . ')';
        }

        // Format payment method (cash -> Tunai, qris -> QRIS, transfer -> Transfer)
        $pmRaw = strtolower($transaction->payment_method ?? 'cash');
        $paymentMethod = match ($pmRaw) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer Bank',
            'debit' => 'Kartu Debit',
            default => ucfirst($pmRaw),
        };

        $isSelfOrder = ($transaction->order_source === 'self_order');

        if ($isSelfOrder) {
            $customerName = htmlspecialchars($transaction->customer_name ?: 'Pelanggan');
            $customerPhone = !empty($transaction->customer_phone) ? ' (' . htmlspecialchars($transaction->customer_phone) . ')' : '';

            $text = "🌐 <b>{$shopName}</b>\n";
            $text .= "Pesanan Online • <code>#{$invoice}</code>\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $text .= "👤 Pemesan : <b>{$customerName}</b>{$customerPhone}\n";
            $text .= "📦 Layanan : {$orderType}\n";
        } else {
            $customerPart = !empty($transaction->customer_name) ? ' • ' . htmlspecialchars($transaction->customer_name) : '';

            $text = "☕ <b>{$shopName}</b>\n";
            $text .= "Nota <code>#{$invoice}</code> • {$orderType}\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $text .= "👤 Kasir   : {$cashier}{$customerPart}\n";
        }

        $text .= "──────────────────────\n";
        $text .= "<b>Pesanan:</b>\n";

        $totalQty = 0;
        foreach ($transaction->details as $detail) {
            $productName = htmlspecialchars($detail->product?->name ?? 'Item');
            $qty = (int) $detail->quantity;
            $totalQty += $qty;
            $subtotal = number_format($detail->subtotal, 0, ',', '.');

            $text .= "▫️ {$qty}x <b>{$productName}</b> — Rp {$subtotal}\n";

            // Addon details
            if (!empty($detail->addons) && is_array($detail->addons)) {
                foreach ($detail->addons as $addon) {
                    $addonName = htmlspecialchars($addon['name'] ?? '');
                    $addonPrice = isset($addon['price']) && $addon['price'] > 0
                        ? ' (+Rp ' . number_format($addon['price'], 0, ',', '.') . ')'
                        : '';
                    if (!empty($addonName)) {
                        $text .= "   └ <i>{$addonName}{$addonPrice}</i>\n";
                    }
                }
            }

            // Catatan item
            if (!empty($detail->notes)) {
                $text .= "   └ <i>Catatan: " . htmlspecialchars($detail->notes) . "</i>\n";
            }
        }

        $text .= "──────────────────────\n";

        $subtotalNominal = number_format($transaction->subtotal, 0, ',', '.');
        $totalNominal = number_format($transaction->total, 0, ',', '.');

        if ((float) $transaction->discount > 0 || (float) $transaction->tax > 0) {
            $text .= "Subtotal   : Rp {$subtotalNominal}\n";
            if ((float) $transaction->discount > 0) {
                $discountNominal = number_format($transaction->discount, 0, ',', '.');
                $text .= "Diskon     : -Rp {$discountNominal}\n";
            }
            if ((float) $transaction->tax > 0) {
                $taxNominal = number_format($transaction->tax, 0, ',', '.');
                $text .= "Pajak      : +Rp {$taxNominal}\n";
            }
        }

        $text .= "<b>Total Bill : Rp {$totalNominal}</b>\n";
        $text .= "Metode     : <b>{$paymentMethod} • Lunas ✅</b>\n";

        if ($pmRaw === 'cash') {
            $paidNominal = number_format($transaction->paid, 0, ',', '.');
            $changeNominal = number_format($transaction->change, 0, ',', '.');
            $text .= "Diterima   : Rp {$paidNominal} (Kembali: Rp {$changeNominal})\n";
        }

        return $text;
    }

    /**
     * Format pesan untuk laporan tutup shift.
     */
    public function formatShiftClosingMessage(CashierShift $shift, ?Setting $setting = null): string
    {
        $shopName = htmlspecialchars($setting?->shop_name ?? 'POS Cafe');
        $cashier = htmlspecialchars($shift->user?->name ?? 'Kasir');
        $startTime = Carbon::parse($shift->start_time)->translatedFormat('d M Y, H:i');
        $endTime = $shift->end_time ? Carbon::parse($shift->end_time)->translatedFormat('H:i') . ' WIB' : 'Sekarang';

        $totalSales = number_format((float) $shift->total_sales, 0, ',', '.');
        $cashSales = number_format((float) $shift->cash_sales, 0, ',', '.');
        $qrisSales = number_format((float) $shift->qris_sales, 0, ',', '.');
        $transferSales = number_format((float) $shift->transfer_sales, 0, ',', '.');
        $nonCashTotal = (float) ($shift->qris_sales ?? 0) + (float) ($shift->transfer_sales ?? 0);
        $nonCashSales = number_format($nonCashTotal, 0, ',', '.');
        $startingCash = number_format((float) $shift->starting_cash, 0, ',', '.');
        $expectedCash = number_format((float) $shift->expected_cash, 0, ',', '.');
        $actualCash = number_format((float) ($shift->actual_cash ?? 0), 0, ',', '.');
        $difference = (float) ($shift->difference ?? 0);
        $totalTransactions = $shift->total_transactions ?? $shift->transactions()->count();

        $totalCashIn = (float) ($shift->total_cash_in ?? 0);
        $totalCashOut = (float) ($shift->total_cash_out ?? 0);
        $cashInFormatted = number_format($totalCashIn, 0, ',', '.');
        $cashOutFormatted = number_format($totalCashOut, 0, ',', '.');

        // Status Selisih Kas Laci
        if ($difference == 0) {
            $diffText = "<b>Rp 0 (Sesuai ✅)</b>";
        } elseif ($difference > 0) {
            $diffNominal = number_format($difference, 0, ',', '.');
            $diffText = "<b>+Rp {$diffNominal} (Surplus 🟢)</b>";
        } else {
            $diffNominal = number_format(abs($difference), 0, ',', '.');
            $diffText = "<b>-Rp {$diffNominal} (Minus ⚠️)</b>";
        }

        // Saldo Kas Riil Toko (Real-Time)
        $storeBalances = CashMovement::getStoreRealBalances();
        $realCash = number_format($storeBalances['cash_balance'], 0, ',', '.');
        $realBank = number_format($storeBalances['bank_balance'], 0, ',', '.');
        $totalReal = number_format($storeBalances['total_real_balance'], 0, ',', '.');

        $text = "📊 <b>{$shopName}</b>\n";
        $text .= "Laporan Tutup Shift • <b>Final</b>\n";
        $text .= "──────────────────────\n";
        $text .= "👤 Kasir     : {$cashier}\n";
        $text .= "🕒 Jam Kerja : {$startTime} – {$endTime}\n";
        $text .= "🧾 Total Nota: {$totalTransactions} Transaksi\n";
        $text .= "──────────────────────\n";

        // 1. Ringkasan Omset
        $text .= "📈 <b>RINGKASAN OMSET</b>\n";
        $text .= "• Tunai        : Rp {$cashSales}\n";
        $text .= "• Non-Tunai    : Rp {$nonCashSales}\n";
        if ((float) $shift->qris_sales > 0 || (float) $shift->transfer_sales > 0) {
            $text .= "  ├ QRIS       : Rp {$qrisSales}\n";
            $text .= "  └ Transfer   : Rp {$transferSales}\n";
        }
        $text .= "💰 <b>Total Omset : Rp {$totalSales}</b>\n";
        $text .= "──────────────────────\n";

        // 2. Kas Laci Kasir
        $text .= "💵 <b>KAS LACI KASIR</b>\n";
        $text .= "• Modal Awal   : Rp {$startingCash}\n";
        if ($totalCashIn > 0) {
            $text .= "• Kas Masuk    : +Rp {$cashInFormatted}\n";
        }
        if ($totalCashOut > 0) {
            $text .= "• Kas Keluar   : -Rp {$cashOutFormatted}\n";
            $shiftMovements = $shift->cashMovements ?? collect();
            $cashOutMovements = $shiftMovements->where('type', 'out');
            if ($cashOutMovements->isNotEmpty()) {
                foreach ($cashOutMovements->take(3) as $m) {
                    $note = !empty($m->notes) ? htmlspecialchars($m->notes) : htmlspecialchars($m->category_name ?: 'Pengeluaran');
                    $amt = number_format((float) $m->amount, 0, ',', '.');
                    $text .= "  └ <i>{$note} (-Rp {$amt})</i>\n";
                }
            }
        }
        $text .= "• Kas Sistem   : Rp {$expectedCash}\n";
        $text .= "• Kas Fisik    : Rp {$actualCash}\n";
        $text .= "⚖️ Selisih Kas : {$diffText}\n";
        $text .= "──────────────────────\n";

        // 3. Saldo Kas Riil Toko
        $text .= "🏦 <b>SALDO KAS RIIL TOKO</b>\n";
        $text .= "• Kas Tunai    : Rp {$realCash}\n";
        $text .= "• Bank / QRIS  : Rp {$realBank}\n";
        $text .= "💼 <b>Total Saldo: Rp {$totalReal}</b>\n";
        $text .= "──────────────────────\n";

        // 4. Rekap Penjualan per Kategori (Ringkas)
        $shiftPortions = [];
        $totalShiftCups = 0;

        $completedTransactions = ($shift->transactions ?? collect())->where('status', 'completed');
        foreach ($completedTransactions as $tx) {
            if (!$tx->relationLoaded('details')) {
                $tx->load('details.product.category');
            }
            foreach ($tx->details as $detail) {
                $catName = $detail->product?->category?->name ?? 'Menu Lainnya';
                $qty = (int) $detail->quantity;

                if (!isset($shiftPortions[$catName])) {
                    $shiftPortions[$catName] = 0;
                }

                $shiftPortions[$catName] += $qty;
                $totalShiftCups += $qty;
            }
        }

        if (!empty($shiftPortions)) {
            $text .= "🏷️ <b>PENJUALAN PER KATEGORI</b>\n";
            foreach ($shiftPortions as $catName => $totalQty) {
                $catIcon = $this->getCategoryIcon($catName);
                $catNameEsc = htmlspecialchars($catName);
                $text .= "{$catIcon} {$catNameEsc} ({$totalQty})\n";
            }
            $text .= "──────────────\n";
            $text .= "📊 <b>Total Cup: {$totalShiftCups} cup</b>\n";
            $text .= "──────────────────────\n";
        }

        $notesContent = $shift->notes ?? $shift->closing_notes ?? null;
        if (!empty($notesContent)) {
            $notes = htmlspecialchars($notesContent);
            $text .= "📝 <i>Catatan: {$notes}</i>\n";
        }

        $text .= "✅ <b>Status: Shift Resmi Ditutup</b>";

        return $text;
    }

    /**
     * Format pesan untuk alert pembatalan nota (Void).
     */
    public function formatVoidMessage(Transaction $transaction, ?Setting $setting = null): string
    {
        $shopName = htmlspecialchars($setting?->shop_name ?? 'POS Cafe');
        $invoice = htmlspecialchars($transaction->invoice_number);
        $cashier = htmlspecialchars($transaction->user?->name ?? 'Kasir');
        $cancelledBy = htmlspecialchars($transaction->cancelledBy?->name ?? (auth()->user()?->name ?? 'Admin/Kasir'));
        $reason = htmlspecialchars($transaction->cancelled_reason ?? 'Tidak ada keterangan');
        $time = Carbon::parse($transaction->cancelled_at ?? now())->translatedFormat('d M Y, H:i') . ' WIB';

        $isSelfOrder = ($transaction->order_source === 'self_order');

        // Format order type (dine_in -> Dine In, takeaway -> Take Away)
        $orderTypeRaw = str_replace('_', ' ', strtolower($transaction->order_type ?? 'dine in'));
        $orderType = ucwords($orderTypeRaw);
        if (!empty($transaction->table_number)) {
            $orderType .= ' (Meja ' . htmlspecialchars($transaction->table_number) . ')';
        }

        // Format payment method (cash -> Tunai, qris -> QRIS, transfer -> Transfer)
        $pmRaw = strtolower($transaction->payment_method ?? '');
        $paymentMethod = match ($pmRaw) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer Bank',
            'debit' => 'Kartu Debit',
            '' => 'Open Bill',
            default => ucfirst($pmRaw),
        };

        if ($isSelfOrder) {
            $customerName = htmlspecialchars($transaction->customer_name ?: 'Pelanggan');
            $customerPhone = !empty($transaction->customer_phone) ? ' (' . htmlspecialchars($transaction->customer_phone) . ')' : '';

            $text = "🚨 <b>{$shopName}</b>\n";
            $text .= "ALERT: TRANSAKSI DIBATALKAN • <code>#{$invoice}</code>\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $customerName = htmlspecialchars($transaction->customer_name ?: 'Pelanggan');
            $text .= "👤 Pemesan   : <b>{$customerName}</b>{$customerPhone}\n";
            $text .= "📦 Layanan   : {$orderType}\n";
            $text .= "🚫 Dibatalkan: <b>{$cancelledBy}</b>\n";
            $text .= "📝 Alasan    : <i>\"{$reason}\"</i>\n";
        } else {
            $text = "🚨 <b>{$shopName}</b>\n";
            $text .= "ALERT: TRANSAKSI DIBATALKAN • <code>#{$invoice}</code>\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $text .= "👤 Kasir     : {$cashier}\n";
            $text .= "📍 Layanan   : {$orderType}\n";
            $text .= "🚫 Dibatalkan: <b>{$cancelledBy}</b>\n";
            $text .= "📝 Alasan    : <i>\"{$reason}\"</i>\n";
        }

        $text .= "──────────────────────\n";
        $text .= "<b>Pesanan Dibatalkan:</b>\n";
        if ($transaction->details && $transaction->details->isNotEmpty()) {
            foreach ($transaction->details as $detail) {
                $productName = htmlspecialchars($detail->product?->name ?? 'Item');
                $qty = (int) $detail->quantity;
                $subtotal = number_format($detail->subtotal, 0, ',', '.');

                $text .= "▫️ {$qty}x <b>{$productName}</b> — Rp {$subtotal}\n";

                // Addon details
                if (!empty($detail->addons) && is_array($detail->addons)) {
                    foreach ($detail->addons as $addon) {
                        $addonName = htmlspecialchars($addon['name'] ?? '');
                        $addonPrice = isset($addon['price']) && $addon['price'] > 0
                            ? ' (+Rp ' . number_format($addon['price'], 0, ',', '.') . ')'
                            : '';
                        if (!empty($addonName)) {
                            $text .= "   └ <i>{$addonName}{$addonPrice}</i>\n";
                        }
                    }
                }

                // Catatan item
                if (!empty($detail->notes)) {
                    $text .= "   └ <i>Catatan: " . htmlspecialchars($detail->notes) . "</i>\n";
                }
            }
        } else {
            $text .= "▫️ <i>(Tidak ada rincian item)</i>\n";
        }

        $text .= "──────────────────────\n";

        $subtotalNominal = number_format($transaction->subtotal, 0, ',', '.');
        $totalNominal = number_format($transaction->total, 0, ',', '.');

        if ((float) $transaction->discount > 0 || (float) $transaction->tax > 0) {
            $text .= "Subtotal   : Rp {$subtotalNominal}\n";
            if ((float) $transaction->discount > 0) {
                $discountNominal = number_format($transaction->discount, 0, ',', '.');
                $text .= "Diskon     : -Rp {$discountNominal}\n";
            }
            if ((float) $transaction->tax > 0) {
                $taxNominal = number_format($transaction->tax, 0, ',', '.');
                $text .= "Pajak      : +Rp {$taxNominal}\n";
            }
        }

        $text .= "<b>Total Bill : Rp {$totalNominal}</b>\n";
        $text .= "Metode     : <b>{$paymentMethod} • Dibatalkan ❌</b>";

        return $text;
    }

    /**
     * Format pesan untuk uji coba koneksi bot.
     */
    public function formatTestMessage(?Setting $setting = null): string
    {
        $shopName = htmlspecialchars($setting?->shop_name ?? 'POS Cafe');
        $time = Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB';

        $text = "🤖 <b>{$shopName}</b>\n";
        $text .= "Uji Coba Notifikasi Bot Telegram\n";
        $text .= "──────────────────────\n";
        $text .= "🕒 Waktu  : {$time}\n";
        $text .= "⚡ Status : <b>Terhubung & Aktif ✅</b>\n";
        $text .= "──────────────────────\n";
        $text .= "Notifikasi transaksi, tutup shift, dan alert void akan otomatis dikirim ke sini.";

        return $text;
    }

    /**
     * Format pesan Telegram untuk arus kas / pengeluaran.
     */
    public function formatCashMovementMessage(CashMovement $movement, ?Setting $setting = null): string
    {
        $shopName = htmlspecialchars($setting?->shop_name ?? 'POS Cafe');
        $userName = htmlspecialchars($movement->user?->name ?? 'Kasir');
        $time = Carbon::parse($movement->movement_date)->translatedFormat('d M Y, H:i') . ' WIB';
        $amount = number_format((float) $movement->amount, 0, ',', '.');
        $isOut = $movement->type === 'out';
        $typeTitle = $isOut ? '🔴 PENGELUARAN KAS (PAY OUT)' : '🟢 KAS MASUK (PAY IN)';
        $category = htmlspecialchars($movement->category_name);
        $source = match ($movement->source) {
            'drawer' => 'Laci Kasir (Cash Drawer)',
            'bank' => 'Rekening Bank / Transfer',
            'petty_cash' => 'Kas Toko / Brankas',
            default => ucfirst($movement->source),
        };

        $text = "💸 <b>{$shopName}</b>\n";
        $text .= "{$typeTitle}\n";
        $text .= "──────────────────────\n";
        $text .= "📄 Ref       : <code>{$movement->movement_number}</code>\n";
        $text .= "🕒 Waktu     : {$time}\n";
        $text .= "👤 Pencatat  : {$userName}\n";
        $text .= "🏷️ Kategori  : <b>{$category}</b>\n";
        $text .= "💳 Sumber    : {$source}\n";
        $text .= "💰 Nominal   : <b>Rp {$amount}</b>\n";
        $notes = htmlspecialchars($movement->notes);
        $text .= "📝 Catatan   : <i>{$notes}</i>\n";

        if ($movement->shift_id && $movement->shift) {
            $expectedCash = number_format((float) $movement->shift->expected_cash, 0, ',', '.');
            $text .= "──────────────────────\n";
            $text .= "💵 Saldo Laci Kasir: Rp {$expectedCash}\n";
        }

        return $text;
    }
}
