<?php

namespace App\Services;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\CashierShift;
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
            $transaction->loadMissing(['details.product', 'user', 'shift']);

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

            $shift->loadMissing(['user', 'transactions']);

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

            $transaction->loadMissing(['user', 'cancelledBy', 'details.product']);

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
     * Kirim notifikasi uji coba (Synchronous).
     */
    public function testNotification(?string $token = null, ?string $chatId = null): array
    {
        $setting = $this->getSetting();
        $message = $this->formatTestMessage($setting);

        return $this->sendMessage($message, $token, $chatId);
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
            $text = "🌐 <b>{$shopName}</b>\n";
            $text .= "Pesanan Online • <code>#{$invoice}</code>\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $customerName = htmlspecialchars($transaction->customer_name ?: 'Pelanggan');
            $text .= "🙋 Pemesan: <b>{$customerName}</b>";
            if (!empty($transaction->customer_phone)) {
                $text .= " (" . htmlspecialchars($transaction->customer_phone) . ")";
            }
            $text .= "\n";
            $text .= "📦 Layanan: {$orderType}\n\n";
        } else {
            $text = "☕ <b>{$shopName}</b>\n";
            $text .= "Nota <code>#{$invoice}</code> • {$orderType}\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $text .= "👤 Kasir: {$cashier}";
            if (!empty($transaction->customer_name)) {
                $text .= " • Pelanggan: " . htmlspecialchars($transaction->customer_name);
            }
            $text .= "\n\n";
        }

        $text .= "<b>Pesanan:</b>\n";
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

        $text .= "──────────────────────\n";

        $subtotalNominal = number_format($transaction->subtotal, 0, ',', '.');
        $totalNominal = number_format($transaction->total, 0, ',', '.');

        if ((float) $transaction->discount > 0 || (float) $transaction->tax > 0) {
            $text .= "Subtotal : Rp {$subtotalNominal}\n";
            if ((float) $transaction->discount > 0) {
                $discountNominal = number_format($transaction->discount, 0, ',', '.');
                $text .= "Diskon   : -Rp {$discountNominal}\n";
            }
            if ((float) $transaction->tax > 0) {
                $taxNominal = number_format($transaction->tax, 0, ',', '.');
                $text .= "Pajak    : +Rp {$taxNominal}\n";
            }
        }

        $text .= "<b>Total    : Rp {$totalNominal}</b>\n";

        if ($pmRaw === 'cash') {
            $paidNominal = number_format($transaction->paid, 0, ',', '.');
            $changeNominal = number_format($transaction->change, 0, ',', '.');
            $text .= "Bayar    : Rp {$paidNominal} (Kembali: Rp {$changeNominal})\n";
        }

        $text .= "\nMetode : <b>{$paymentMethod}</b> • <b>Lunas ✅</b>";

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

        $text = "📊 <b>{$shopName}</b>\n";
        $text .= "Laporan Tutup Shift • <b>Final</b>\n";
        $text .= "──────────────────────\n";
        $text .= "👤 Kasir     : {$cashier}\n";
        $text .= "🕒 Jam Kerja : {$startTime} – {$endTime}\n";
        $text .= "📦 Total Nota: {$totalTransactions} Transaksi\n";
        $text .= "──────────────────────\n";
        $text .= "💵 Saldo Awal: Rp {$startingCash}\n";
        $text .= "💰 Tunai     : Rp {$cashSales}\n";
        $text .= "💳 Non-Tunai : Rp {$nonCashSales}\n";
        if ((float) $shift->qris_sales > 0 || (float) $shift->transfer_sales > 0) {
            $text .= "   ├ QRIS     : Rp {$qrisSales}\n";
            $text .= "   └ Transfer : Rp {$transferSales}\n";
        }
        $text .= "📈 <b>Total Omset : Rp {$totalSales}</b>\n";
        $text .= "──────────────────────\n";
        $text .= "💵 Kas Fisik : Rp {$actualCash} (Sistem: Rp {$expectedCash})\n";

        if ($difference == 0) {
            $text .= "⚖️ Selisih   : <b>Rp 0 (Sesuai ✅)</b>\n";
        } elseif ($difference > 0) {
            $diffNominal = number_format($difference, 0, ',', '.');
            $text .= "⚖️ Selisih   : <b>+Rp {$diffNominal} (Surplus 🟢)</b>\n";
        } else {
            $diffNominal = number_format(abs($difference), 0, ',', '.');
            $text .= "⚖️ Selisih   : <b>-Rp {$diffNominal} (Minus ⚠️)</b>\n";
        }

        $notesContent = $shift->notes ?? $shift->closing_notes ?? null;
        if (!empty($notesContent)) {
            $notes = htmlspecialchars($notesContent);
            $text .= "📝 Catatan   : <i>{$notes}</i>\n";
        }

        $text .= "\n<b>Status: Shift Ditutup Resmi ✅</b>";

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
            $text = "🚨 <b>{$shopName}</b>\n";
            $text .= "Pembatalan Online • <code>#{$invoice}</code>\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $customerName = htmlspecialchars($transaction->customer_name ?: 'Pelanggan');
            $text .= "🙋 Pemesan: <b>{$customerName}</b>";
            if (!empty($transaction->customer_phone)) {
                $text .= " (" . htmlspecialchars($transaction->customer_phone) . ")";
            }
            $text .= "\n";
            $text .= "📦 Layanan: {$orderType}\n";
            $text .= "🚫 Batal: <b>{$cancelledBy}</b>\n";
            $text .= "📝 Alasan: <i>\"{$reason}\"</i>\n\n";
        } else {
            $text = "🚨 <b>{$shopName}</b>\n";
            $text .= "Pembatalan Nota • <code>#{$invoice}</code> • {$orderType}\n";
            $text .= "──────────────────────\n";
            $text .= "🕒 {$time}\n";
            $text .= "👤 Kasir: {$cashier}";
            if (!empty($transaction->customer_name)) {
                $text .= " • Pelanggan: " . htmlspecialchars($transaction->customer_name);
            }
            $text .= "\n";
            $text .= "🚫 Batal: <b>{$cancelledBy}</b>\n";
            $text .= "📝 Alasan: <i>\"{$reason}\"</i>\n\n";
        }

        $text .= "<b>Pesanan:</b>\n";
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
            $text .= "Subtotal : Rp {$subtotalNominal}\n";
            if ((float) $transaction->discount > 0) {
                $discountNominal = number_format($transaction->discount, 0, ',', '.');
                $text .= "Diskon   : -Rp {$discountNominal}\n";
            }
            if ((float) $transaction->tax > 0) {
                $taxNominal = number_format($transaction->tax, 0, ',', '.');
                $text .= "Pajak    : +Rp {$taxNominal}\n";
            }
        }

        $text .= "<b>Total    : Rp {$totalNominal}</b>\n";
        $text .= "\nMetode : <b>{$paymentMethod}</b> • <b>Dibatalkan ❌</b>";

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
}
