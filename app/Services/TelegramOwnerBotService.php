<?php

namespace App\Services;

use App\Exports\ExecutiveSalesReportExport;
use App\Exports\TopProductsExport;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\CashMovement;
use App\Models\CashierShift;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TelegramOwnerBotService
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Dapatkan Bot Token yang digunakan untuk Bot Owner.
     */
    public function getBotToken(): ?string
    {
        $setting = $this->telegramService->getSetting();
        $token = config('services.telegram_owner.bot_token')
            ?: ($setting?->telegram_owner_bot_token ?: $this->telegramService->getBotToken($setting));

        return !empty($token) ? trim($token) : null;
    }

    /**
     * Dapatkan Chat ID Owner yang terdaftar.
     */
    public function getChatId(): ?string
    {
        $setting = $this->telegramService->getSetting();
        $chatId = config('services.telegram_owner.chat_id')
            ?: ($setting?->telegram_owner_chat_id ?: $this->telegramService->getChatId($setting));

        return !empty($chatId) ? trim($chatId) : null;
    }

    /**
     * Cek apakah Chat ID atau User ID berhak mengakses bot ini (Whitelist).
     */
    public function isAuthorized(string $chatId, ?string $userId = null): bool
    {
        $targetChatId = $this->getChatId();

        // 1. Cocok dengan Chat ID terdaftar (Japri atau Private Group)
        if (!empty($targetChatId) && (string) $chatId === (string) $targetChatId) {
            return true;
        }

        // 2. Cocok dengan User ID pengirim
        if (!empty($targetChatId) && !empty($userId) && (string) $userId === (string) $targetChatId) {
            return true;
        }

        // 3. Whitelist admin IDs tambahan dari .env (contoh: TELEGRAM_OWNER_ADMIN_IDS=123,456)
        $adminIdsRaw = config('services.telegram_owner.admin_ids', '');
        if (!empty($adminIdsRaw)) {
            $allowedList = array_map('trim', explode(',', $adminIdsRaw));
            if (in_array((string) $chatId, $allowedList, true) || (!empty($userId) && in_array((string) $userId, $allowedList, true))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle webhook update dari Telegram Bot API.
     */
    public function handleWebhookUpdate(array $update): void
    {
        try {
            if (isset($update['message'])) {
                $this->handleIncomingMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
        } catch (\Throwable $e) {
            Log::error('TelegramOwnerBotService Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle incoming text message / command.
     */
    protected function handleIncomingMessage(array $message): void
    {
        $chatId = (string) ($message['chat']['id'] ?? '');
        $userId = (string) ($message['from']['id'] ?? '');
        $text = trim($message['text'] ?? '');
        $fromName = trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''));

        if (empty($chatId) || empty($text)) {
            return;
        }

        // Otorisasi Keamanan
        if (!$this->isAuthorized($chatId, $userId)) {
            $this->sendUnauthorizedReply($chatId);
            return;
        }

        $cleanText = strtolower($text);
        if (str_contains($cleanText, '@')) {
            $cleanText = explode('@', $cleanText)[0];
        }

        // 1. Cek Smart Custom Date Parser (misal: "01/10/2026 - 15/10/2026", "menu 1 - 2 okt", "rekap september")
        $isMenuQuery = (bool) preg_match('/\b(menu|laris|terlaris|top)\b/i', $cleanText);
        $customRange = $this->parseDateRangeFromText($text);
        if ($customRange) {
            if ($isMenuQuery) {
                $this->sendTopProductsByDateRange(
                    $chatId,
                    $customRange['from'],
                    $customRange['to'],
                    $customRange['title']
                );
            } else {
                $this->sendFinancialCard(
                    $chatId,
                    $customRange['from'],
                    $customRange['to'],
                    $customRange['title']
                );
            }
            return;
        }

        // 2. Cek Command & Reply Keyboard Clicks
        switch ($cleanText) {
            case '/start':
            case '/menu':
            case 'menu':
                $this->sendWelcomeDashboard($chatId, $fromName);
                break;

            case '⚡ rekap hari ini':
            case 'rekap hari ini':
            case 'hari ini':
            case 'today':
            case '/today':
            case '/hariini':
                $today = Carbon::today()->format('Y-m-d');
                $this->sendFinancialCard($chatId, $today, $today, 'Hari Ini (' . Carbon::now()->translatedFormat('d M Y') . ')');
                break;

            case '⏮️ rekap kemarin':
            case 'rekap kemarin':
            case 'kemarin':
            case 'yesterday':
            case '/yesterday':
            case '/kemarin':
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $this->sendFinancialCard($chatId, $yesterday, $yesterday, 'Kemarin (' . Carbon::yesterday()->translatedFormat('d M Y') . ')');
                break;

            case '📆 7 hari terakhir':
            case '7 hari terakhir':
            case '7 hari':
            case 'minggu ini':
            case 'seminggu':
            case '/week':
            case '/minggu':
                $start = Carbon::now()->subDays(6)->format('Y-m-d');
                $end = Carbon::today()->format('Y-m-d');
                $this->sendFinancialCard($chatId, $start, $end, '7 Hari Terakhir');
                break;

            case '🗓️ rekap bulan ini':
            case 'rekap bulan ini':
            case 'bulan ini':
            case 'month':
            case '/month':
            case '/bulan':
                $start = Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = Carbon::today()->format('Y-m-d');
                $this->sendFinancialCard($chatId, $start, $end, 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')');
                break;

            case '📥 download excel':
            case 'download excel':
            case 'export excel':
            case 'excel':
            case '/excel':
                $this->sendExcelPeriodPicker($chatId);
                break;

            case '🏆 menu terlaris':
            case 'menu terlaris':
            case 'terlaris':
            case 'top seller':
            case 'best seller':
            case 'laris':
            case '/laris':
            case '/top':
                $this->sendTopProductsReport($chatId, 'today');
                break;

            case '💡 laba & performa':
            case 'laba & performa':
            case 'laba':
            case 'profit':
            case '/laba':
            case '/profit':
                $this->sendProfitDetailReport($chatId);
                break;

            case '❓ bantuan':
            case 'bantuan':
            case 'help':
            case '/help':
                $this->sendHelp($chatId);
                break;

            default:
                // Jika tidak cocok apa pun, kirim welcome menu dan keyboard
                $this->sendWelcomeDashboard($chatId, $fromName, "Perintah <code>{$text}</code> tidak dikenali. Silakan pilih menu di bawah keyboard atau ketik rentang tanggal.");
                break;
        }
    }

    /**
     * Handle klik tombol inline (callback_query).
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $chatId = (string) ($callbackQuery['message']['chat']['id'] ?? '');
        $messageId = (int) ($callbackQuery['message']['message_id'] ?? 0);
        $userId = (string) ($callbackQuery['from']['id'] ?? '');
        $action = trim($callbackQuery['data'] ?? '');

        if (empty($chatId) || empty($action)) {
            return;
        }

        if (!$this->isAuthorized($chatId, $userId)) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Akses ditolak.', true, $this->getBotToken());
            return;
        }

        // 1. Download Top Products Excel: owner_dl_top:<from>:<to>
        if (str_starts_with($action, 'owner_dl_top:')) {
            $payload = substr($action, strlen('owner_dl_top:'));
            $parts = explode(':', $payload);
            $from = $parts[0] ?? Carbon::today()->format('Y-m-d');
            $to = $parts[1] ?? $from;

            $this->sendTopProductsExcelReport($chatId, $from, $to, $callbackId);
            return;
        }

        // 2. Download Sales Excel Action: owner_dl_excel:<from>:<to>
        if (str_starts_with($action, 'owner_dl_excel:') || str_starts_with($action, 'owner_dl_sales:')) {
            $payload = str_starts_with($action, 'owner_dl_excel:')
                ? substr($action, strlen('owner_dl_excel:'))
                : substr($action, strlen('owner_dl_sales:'));
            $parts = explode(':', $payload);
            $from = $parts[0] ?? Carbon::today()->format('Y-m-d');
            $to = $parts[1] ?? $from;

            $this->sendExcelReport($chatId, $from, $to, null, $callbackId);
            return;
        }

        // 2. Refresh / Re-render Financial Card: owner_card:<from>:<to>
        if (str_starts_with($action, 'owner_card:')) {
            $payload = substr($action, strlen('owner_card:'));
            $parts = explode(':', $payload);
            $from = $parts[0] ?? Carbon::today()->format('Y-m-d');
            $to = $parts[1] ?? $from;
            $title = $parts[2] ?? ($from === $to ? Carbon::parse($from)->translatedFormat('d M Y') : 'Periode ' . Carbon::parse($from)->format('d/m') . ' - ' . Carbon::parse($to)->format('d/m/Y'));

            $this->sendFinancialCard($chatId, $from, $to, $title, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId, 'Data diperbarui 🔄', false, $this->getBotToken());
            return;
        }

        // 3. Menu Picker Excel
        if ($action === 'owner_menu_excel') {
            $this->sendExcelPeriodPicker($chatId, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId, null, false, $this->getBotToken());
            return;
        }

        // 4. Tutup Pesan
        if ($action === 'owner_close') {
            $this->telegramService->answerCallbackQuery($callbackId, 'Pesan ditutup', false, $this->getBotToken());
            if ($messageId > 0) {
                $this->telegramService->deleteMessage($this->getBotToken(), $chatId, $messageId);
            }
            return;
        }

        // 5. Menu Terlaris Range Callback: owner_top_range:<from>:<to>
        if (str_starts_with($action, 'owner_top_range:')) {
            $payload = substr($action, strlen('owner_top_range:'));
            $parts = explode(':', $payload);
            $from = $parts[0] ?? Carbon::today()->format('Y-m-d');
            $to = $parts[1] ?? $from;

            $this->sendTopProductsByDateRange($chatId, $from, $to, null, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId, 'Memuat Menu Terlaris 🏆', false, $this->getBotToken());
            return;
        }

        // 6. Menu Terlaris Preset Callback: owner_top:<period>
        if (str_starts_with($action, 'owner_top:')) {
            $period = substr($action, strlen('owner_top:'));
            $this->sendTopProductsReport($chatId, $period, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId, null, false, $this->getBotToken());
            return;
        }

        $this->telegramService->answerCallbackQuery($callbackId, null, false, $this->getBotToken());
    }

    /**
     * Balasan jika akses ditolak (Bukan Owner / Tidak Whitelisted).
     */
    protected function sendUnauthorizedReply(string $chatId): void
    {
        $text = "⛔ <b>Akses Khusus Owner</b>\n\n";
        $text .= "Akun Telegram Anda tidak terdaftar dalam whitelist manajemen eksekutif.\n";
        $text .= "🆔 <b>ID Anda:</b> <code>{$chatId}</code>\n\n";
        $text .= "<i>Hubungi administrator sistem untuk mendaftarkan ID ini pada <code>TELEGRAM_OWNER_CHAT_ID</code>.</i>";

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId);
    }

    /**
     * Kirim Welcome Dashboard dengan persistent Reply Keyboard di bawah input chat.
     */
    public function sendWelcomeDashboard(string $chatId, string $fromName = '', ?string $notice = null, ?int $messageId = null): void
    {
        $setting = $this->telegramService->getSetting();
        $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));
        $todayFormatted = Carbon::now()->translatedFormat('l, d F Y • H:i') . ' WIB';

        $greeting = !empty($fromName) ? "Halo, <b>{$fromName}</b>!" : "Halo, <b>Bos!</b>";

        $text = "🏪 <b>{$shopName} — EXECUTIVE ASSISTANT</b>\n";
        $text .= "🕒 <i>{$todayFormatted}</i>\n";
        $text .= "────────────────────────────\n";
        if ($notice) {
            $text .= "⚠️ <i>{$notice}</i>\n\n";
        }
        $text .= "{$greeting} Siap memantau performa cafe hari ini?\n\n";
        $text .= "Pilih menu rekap cepat di bawah keyboard, atau ketik langsung tanggal yang diinginkan (misal: <code>25 sep - 30 sep</code>).\n";
        $text .= "────────────────────────────";

        $replyMarkup = $this->getPersistentKeyboard();

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $replyMarkup);
    }

    /**
     * Financial Card Dashboard yang modern, estetik, dan informatif.
     */
    public function sendFinancialCard(string $chatId, string $dateFrom, string $dateTo, string $periodTitle, ?int $messageId = null): void
    {
        $start = $dateFrom . ' 00:00:00';
        $end = $dateTo . ' 23:59:59';

        $setting = $this->telegramService->getSetting();
        $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));

        // Query Transaksi Selesai
        $transactions = Transaction::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhere(function ($sq) {
                        $sq->where('order_source', 'self_order')
                            ->where('payment_status', 'paid')
                            ->whereNotIn('status', ['cancelled', 'pending']);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', '!=', 'unpaid')
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $totalOrders = $transactions->count();
        $avgBasket = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

        // Total Cup & Profit
        $txIds = $transactions->pluck('id');
        $totalCups = (int) TransactionDetail::whereIn('transaction_id', $txIds)->sum('quantity');
        $grossProfit = (float) TransactionDetail::whereIn('transaction_id', $txIds)->sum('profit');
        $marginPercent = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // Kas Keluar Operasional
        $cashOut = (float) CashMovement::where('type', 'out')
            ->whereBetween('movement_date', [$start, $end])
            ->sum('amount');

        $netProfit = $grossProfit - $cashOut;

        // Breakdown Pembayaran
        $cashSales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'cash')->sum('total');
        $qrisSales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'qris')->sum('total');
        $transferSales = (float) $transactions->filter(fn($t) => in_array(strtolower($t->payment_method ?? ''), ['transfer', 'debit']))->sum('total');

        $qrisPct = $totalRevenue > 0 ? round(($qrisSales / $totalRevenue) * 100) : 0;
        $cashPct = $totalRevenue > 0 ? round(($cashSales / $totalRevenue) * 100) : 0;
        $transferPct = $totalRevenue > 0 ? round(($transferSales / $totalRevenue) * 100) : 0;

        // Peak Hours
        $peakHourRow = Transaction::select(DB::raw('HOUR(created_at) as hr'), DB::raw('COUNT(*) as total_tx'))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderByDesc('total_tx')
            ->first();

        $peakHourStr = $peakHourRow ? sprintf('%02d:00 - %02d:00 WIB', $peakHourRow->hr, ($peakHourRow->hr + 1) % 24) : '-';

        // Hitung Komparasi Periode Sebelumnya (Growth %)
        $fromDate = Carbon::parse($dateFrom)->startOfDay();
        $toDate = Carbon::parse($dateTo)->endOfDay();
        $diffDays = max(1, $fromDate->diffInDays($toDate) + 1);

        $prevEnd = $fromDate->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($diffDays - 1)->startOfDay();

        $prevRevenue = (float) Transaction::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'completed')
            ->sum('total');

        $growthStr = '';
        if ($prevRevenue > 0) {
            $growth = round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1);
            if ($growth > 0) {
                $growthStr = " <i>(+{$growth}% vs lalu 🚀)</i>";
            } elseif ($growth < 0) {
                $growthStr = " <i>({$growth}% vs lalu 🔻)</i>";
            } else {
                $growthStr = " <i>(stabil 0%)</i>";
            }
        }

        // Susun Teks Financial Card
        $text = "🏪 <b>{$shopName} • FINANCIAL CARD</b>\n";
        $text .= "🗓️ <b>{$periodTitle}</b>\n";
        $text .= "────────────────────────────\n";
        $text .= "💰 <b>TOTAL REVENUE</b>\n";
        $text .= "<code>Rp " . number_format($totalRevenue, 0, ',', '.') . "</code>{$growthStr}\n\n";

        $text .= "📈 <b>PROFIT & CASHFLOW</b>\n";
        $text .= "• Gross Profit : <code>Rp " . number_format($grossProfit, 0, ',', '.') . "</code> <i>(Margin {$marginPercent}%)</i>\n";
        $text .= "• Kas Keluar   : <code>Rp " . number_format($cashOut, 0, ',', '.') . "</code>\n";
        $profitSign = $netProfit >= 0 ? '🟢' : '🔴';
        $text .= "• <b>Net Profit   : Rp " . number_format($netProfit, 0, ',', '.') . "</b> {$profitSign}\n\n";

        $text .= "📊 <b>METRIK PERFORMA</b>\n";
        $text .= "• Total Order  : <b>{$totalOrders} transaksi</b>\n";
        $text .= "• Avg Spending : <code>Rp " . number_format($avgBasket, 0, ',', '.') . "</code> / order\n";
        $text .= "• Total Cup    : <b>{$totalCups} cup terjual</b>\n";
        $text .= "• Jam Ramai    : <b>{$peakHourStr}</b> 🔥\n\n";

        $text .= "💳 <b>PAYMENT SPLIT</b>\n";
        $text .= "<code>QRIS </code> " . $this->generateProgressBar($qrisPct) . " {$qrisPct}% <code>Rp " . number_format($qrisSales, 0, ',', '.') . "</code>\n";
        $text .= "<code>CASH </code> " . $this->generateProgressBar($cashPct) . " {$cashPct}% <code>Rp " . number_format($cashSales, 0, ',', '.') . "</code>\n";
        if ($transferSales > 0) {
            $text .= "<code>TRF  </code> " . $this->generateProgressBar($transferPct) . " {$transferPct}% <code>Rp " . number_format($transferSales, 0, ',', '.') . "</code>\n";
        }
        $text .= "────────────────────────────";

        $inlineKeyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🏆 Cek Menu Terlaris Periode Ini', 'callback_data' => "owner_top_range:{$dateFrom}:{$dateTo}"],
                ],
                [
                    ['text' => '📥 Excel Penjualan', 'callback_data' => "owner_dl_sales:{$dateFrom}:{$dateTo}"],
                    ['text' => '📥 Excel Menu', 'callback_data' => "owner_dl_top:{$dateFrom}:{$dateTo}"],
                ],
                [
                    ['text' => '🔄 Refresh Data', 'callback_data' => "owner_card:{$dateFrom}:{$dateTo}"],
                    ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                ],
            ],
        ];

        if (!empty($messageId)) {
            $editRes = $this->telegramService->editMessageText(
                $text,
                $this->getBotToken(),
                $chatId,
                $messageId,
                $inlineKeyboard
            );

            if (($editRes['success'] ?? false) || ($editRes['not_modified'] ?? false)) {
                return;
            }
        }

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $inlineKeyboard);
    }

    /**
     * Generate file Excel Penjualan & Transaksi dan kirim langsung via Telegram sendDocument.
     */
    public function sendExcelReport(string $chatId, string $dateFrom, string $dateTo, ?string $periodTitle = null, ?string $callbackId = null): void
    {
        $botToken = $this->getBotToken();

        // 1. Berikan notifikasi loading ke Telegram
        if (!empty($callbackId)) {
            $this->telegramService->answerCallbackQuery($callbackId, '⏳ Sedang meracik file Excel...', false, $botToken);
        }

        $this->telegramService->sendChatAction('upload_document', $chatId, $botToken);

        try {
            $setting = $this->telegramService->getSetting();
            $shopName = $setting?->shop_name ?? 'POS CAFE';

            // Format nama file ringkas hh-bb-yyyy (DD-MM-YYYY)
            if ($dateFrom === $dateTo) {
                $dateStr = Carbon::parse($dateFrom)->format('d-m-Y');
                $fileName = "Penjualan_{$dateStr}.xlsx";
                $periodDisplay = Carbon::parse($dateFrom)->translatedFormat('d F Y');
            } else {
                $fromStr = Carbon::parse($dateFrom)->format('d-m-Y');
                $toStr = Carbon::parse($dateTo)->format('d-m-Y');
                $fileName = "Penjualan_{$fromStr}_sd_{$toStr}.xlsx";
                $periodDisplay = Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . Carbon::parse($dateTo)->format('d/m/Y');
            }

            $periodTitle = $periodTitle ?: $periodDisplay;
            $relativePath = "temp_reports/{$fileName}";

            // Export ke local disk
            Excel::store(new ExecutiveSalesReportExport($dateFrom, $dateTo), $relativePath, 'local');

            $fullPath = Storage::disk('local')->path($relativePath);

            if (!file_exists($fullPath)) {
                throw new \RuntimeException("Gagal menyimpan file Excel ke server: {$fullPath}");
            }

            $caption = "📊 <b>LAPORAN KEUANGAN & PENJUALAN EKSEKUTIF</b>\n";
            $caption .= "🏪 <b>{$shopName}</b>\n";
            $caption .= "🗓️ Periode: <b>{$periodTitle}</b>\n";
            $caption .= "🕒 Diunduh: <i>" . Carbon::now()->translatedFormat('d M Y, H:i') . " WIB</i>\n\n";
            $caption .= "<i>✨ <b>Executive Finance Model:</b> KPI Scorecards, Pie Chart Metode Bayar, Detail Transaksi, & Grafik Menu Terlaris.</i>";

            // Kirim dokumen via Telegram Service
            $sendRes = $this->telegramService->sendDocument(
                $fullPath,
                $caption,
                $botToken,
                $chatId,
                $fileName
            );

            // Bersihkan file temporary dari storage
            if (Storage::disk('local')->exists($relativePath)) {
                Storage::disk('local')->delete($relativePath);
            }

            if (!$sendRes['success']) {
                $this->telegramService->sendMessage(
                    "❌ Gagal mengirim file Excel: " . ($sendRes['message'] ?? 'Error tidak diketahui'),
                    $botToken,
                    $chatId
                );
            }
        } catch (\Throwable $e) {
            Log::error('Send Excel to Telegram Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->telegramService->sendMessage(
                "❌ Terjadi kesalahan saat memproses Excel: " . $e->getMessage(),
                $botToken,
                $chatId
            );
        }
    }

    /**
     * Generate file Excel khusus Menu Terlaris dan kirim langsung via Telegram sendDocument.
     */
    public function sendTopProductsExcelReport(string $chatId, string $dateFrom, string $dateTo, ?string $callbackId = null): void
    {
        $botToken = $this->getBotToken();

        if (!empty($callbackId)) {
            $this->telegramService->answerCallbackQuery($callbackId, '⏳ Sedang meracik Excel Menu Terlaris...', false, $botToken);
        }

        $this->telegramService->sendChatAction('upload_document', $chatId, $botToken);

        try {
            $setting = $this->telegramService->getSetting();
            $shopName = $setting?->shop_name ?? 'POS CAFE';

            // Format nama file ringkas hh-bb-yyyy (DD-MM-YYYY)
            if ($dateFrom === $dateTo) {
                $dateStr = Carbon::parse($dateFrom)->format('d-m-Y');
                $fileName = "Menu_Terlaris_{$dateStr}.xlsx";
                $periodDisplay = Carbon::parse($dateFrom)->translatedFormat('d F Y');
            } else {
                $fromStr = Carbon::parse($dateFrom)->format('d-m-Y');
                $toStr = Carbon::parse($dateTo)->format('d-m-Y');
                $fileName = "Menu_Terlaris_{$fromStr}_sd_{$toStr}.xlsx";
                $periodDisplay = Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . Carbon::parse($dateTo)->format('d/m/Y');
            }

            $relativePath = "temp_reports/{$fileName}";

            // Export ke local disk menggunakan TopProductsExport khusus
            Excel::store(new TopProductsExport($dateFrom, $dateTo), $relativePath, 'local');

            $fullPath = Storage::disk('local')->path($relativePath);

            if (!file_exists($fullPath)) {
                throw new \RuntimeException("Gagal menyimpan file Excel ke server: {$fullPath}");
            }

            $caption = "🏆 <b>LEADERBOARD & PROFITABILITAS MENU</b>\n";
            $caption .= "🏪 <b>{$shopName}</b>\n";
            $caption .= "🗓️ Periode: <b>{$periodDisplay}</b>\n";
            $caption .= "🕒 Diunduh: <i>" . Carbon::now()->translatedFormat('d M Y, H:i') . " WIB</i>\n\n";
            $caption .= "<i>✨ <b>Executive Finance Model:</b> Scorecard Omset & Profit, Ranking Menu, Margin Keuntungan, & Grafik Batang Penjualan.</i>";

            // Kirim dokumen via Telegram Service
            $sendRes = $this->telegramService->sendDocument(
                $fullPath,
                $caption,
                $botToken,
                $chatId,
                $fileName
            );

            // Bersihkan file temporary dari storage
            if (Storage::disk('local')->exists($relativePath)) {
                Storage::disk('local')->delete($relativePath);
            }

            if (!$sendRes['success']) {
                $this->telegramService->sendMessage(
                    "❌ Gagal mengirim file Excel: " . ($sendRes['message'] ?? 'Error tidak diketahui'),
                    $botToken,
                    $chatId
                );
            }
        } catch (\Throwable $e) {
            Log::error('Send Top Products Excel Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->telegramService->sendMessage(
                "❌ Terjadi kesalahan saat memproses Excel: " . $e->getMessage(),
                $botToken,
                $chatId
            );
        }
    }

    /**
     * Tampilkan menu pilihan periode unduh Excel.
     */
    public function sendExcelPeriodPicker(string $chatId, ?int $messageId = null): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $weekStart = Carbon::now()->subDays(6)->format('Y-m-d');
        $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        $text = "📥 <b>PUSAT UNDUH EXCEL EKSEKUTIF</b>\n";
        $text .= "────────────────────────────\n";
        $text .= "Silakan pilih periode laporan yang ingin di-generate menjadi file spreadsheet <b>.xlsx</b>:\n\n";
        $text .= "• Format mencakup multi-sheet (KPI Summary, Detail Transaksi, dan Ranking Menu).\n";
        $text .= "• Bisa langsung dibuka di Microsoft Excel HP, WPS, atau Google Sheets.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📄 Excel Hari Ini', 'callback_data' => "owner_dl_excel:{$today}:{$today}"],
                    ['text' => '📄 Excel Kemarin', 'callback_data' => "owner_dl_excel:{$yesterday}:{$yesterday}"],
                ],
                [
                    ['text' => '📄 Excel 7 Hari Terakhir', 'callback_data' => "owner_dl_excel:{$weekStart}:{$today}"],
                    ['text' => '📄 Excel Bulan Ini', 'callback_data' => "owner_dl_excel:{$monthStart}:{$today}"],
                ],
                [
                    ['text' => '📄 Excel Bulan Lalu (Full)', 'callback_data' => "owner_dl_excel:{$lastMonthStart}:{$lastMonthEnd}"],
                ],
                [
                    ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                ],
            ],
        ];

        if (!empty($messageId)) {
            $this->telegramService->editMessageText($text, $this->getBotToken(), $chatId, $messageId, $keyboard);
            return;
        }

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $keyboard);
    }

    /**
     * Tampilkan Laporan Menu Terlaris (Leaderboard Produk) preset (today, week, month).
     */
    public function sendTopProductsReport(string $chatId, string $period = 'today', ?int $messageId = null): void
    {
        if ($period === 'month') {
            $from = Carbon::now()->startOfMonth()->format('Y-m-d');
            $to = Carbon::today()->format('Y-m-d');
            $periodLabel = 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')';
        } elseif ($period === 'week') {
            $from = Carbon::now()->subDays(6)->format('Y-m-d');
            $to = Carbon::today()->format('Y-m-d');
            $periodLabel = '7 Hari Terakhir';
        } else {
            $period = 'today';
            $from = Carbon::today()->format('Y-m-d');
            $to = Carbon::today()->format('Y-m-d');
            $periodLabel = 'Hari Ini (' . Carbon::now()->translatedFormat('d M Y') . ')';
        }

        $this->sendTopProductsByDateRange($chatId, $from, $to, $periodLabel, $messageId, $period);
    }

    /**
     * Tampilkan Laporan Menu Terlaris (Leaderboard Produk) berdasarkan rentang tanggal bebas.
     */
    public function sendTopProductsByDateRange(
        string $chatId,
        string $dateFrom,
        string $dateTo,
        ?string $periodTitle = null,
        ?int $messageId = null,
        ?string $activePreset = null
    ): void {
        $setting = $this->telegramService->getSetting();
        $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));

        $start = $dateFrom . ' 00:00:00';
        $end = $dateTo . ' 23:59:59';

        if (empty($periodTitle)) {
            if ($dateFrom === $dateTo) {
                $periodTitle = Carbon::parse($dateFrom)->translatedFormat('d F Y');
            } else {
                $periodTitle = Carbon::parse($dateFrom)->translatedFormat('d M Y') . ' - ' . Carbon::parse($dateTo)->translatedFormat('d M Y');
            }
        }

        $totalRevenueAll = (float) Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->sum('total');

        $topProducts = TransactionDetail::select(
            'products.id',
            'products.name',
            'categories.name as category_name',
            DB::raw('SUM(transaction_details.quantity) as total_sold'),
            DB::raw('SUM(transaction_details.subtotal) as total_nominal')
        )
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $text = "🏆 <b>{$shopName} • MENU TERLARIS</b>\n";
        $text .= "🗓️ <b>Periode: {$periodTitle}</b>\n";
        $text .= "────────────────────────────\n";

        if ($topProducts->isEmpty()) {
            $text .= "<i>Belum ada transaksi penjualan pada periode ini.</i>\n";
        } else {
            $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'];
            $idx = 0;

            foreach ($topProducts as $item) {
                $icon = $medals[$idx] ?? '▫️';
                $name = htmlspecialchars($item->name ?? 'Produk');
                $sold = (int) $item->total_sold;
                $nominal = (float) $item->total_nominal;
                $share = $totalRevenueAll > 0 ? round(($nominal / $totalRevenueAll) * 100) : 0;

                $text .= "{$icon} <b>{$name}</b>\n";
                $text .= "   └ 🥤 <b>{$sold} terjual</b> • <code>Rp " . number_format($nominal, 0, ',', '.') . "</code> <i>({$share}%)</i>\n\n";
                $idx++;
            }
        }

        $text .= "────────────────────────────";

        $btnToday = ($activePreset === 'today') ? '• Hari Ini •' : '📅 Hari Ini';
        $btnWeek = ($activePreset === 'week') ? '• 7 Hari •' : '📆 7 Hari';
        $btnMonth = ($activePreset === 'month') ? '• Bulan Ini •' : '🗓️ Bulan Ini';

        $inlineKeyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💰 Lihat Rekap Omset & Finansial', 'callback_data' => "owner_card:{$dateFrom}:{$dateTo}"],
                ],
                [
                    ['text' => $btnToday, 'callback_data' => 'owner_top:today'],
                    ['text' => $btnWeek, 'callback_data' => 'owner_top:week'],
                    ['text' => $btnMonth, 'callback_data' => 'owner_top:month'],
                ],
                [
                    ['text' => '📥 Excel Menu', 'callback_data' => "owner_dl_top:{$dateFrom}:{$dateTo}"],
                    ['text' => '📥 Excel Penjualan', 'callback_data' => "owner_dl_sales:{$dateFrom}:{$dateTo}"],
                ],
                [
                    ['text' => '🔄 Refresh', 'callback_data' => "owner_top_range:{$dateFrom}:{$dateTo}"],
                    ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                ],
            ],
        ];

        if (!empty($messageId)) {
            $editRes = $this->telegramService->editMessageText(
                $text,
                $this->getBotToken(),
                $chatId,
                $messageId,
                $inlineKeyboard
            );

            if (($editRes['success'] ?? false) || ($editRes['not_modified'] ?? false)) {
                return;
            }
        }

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $inlineKeyboard);
    }

    /**
     * Panduan input rentang tanggal kustom.
     */
    public function sendCustomDatePrompt(string $chatId, ?int $messageId = null): void
    {
        $text = "🔍 <b>PILIH RENTANG TANGGAL BEBAS</b>\n";
        $text .= "────────────────────────────\n";
        $text .= "Anda dapat mengetik rentang tanggal apa pun langsung di chat ini.\n\n";
        $text .= "<b>Contoh format yang didukung:</b>\n";
        $text .= "• <code>1 oktober - 2 oktober</code> <i>(atau '1 - 5 oktober')</i>\n";
        $text .= "• <code>1 oktober</code> <i>(rekap 1 hari spesifik)</i>\n";
        $text .= "• <code>01/10/2026 - 15/10/2026</code>\n";
        $text .= "• <code>rekap september 2026</code> <i>(atau 'september')</i>\n\n";
        $text .= "Bot akan langsung menyajikan ringkasan finansial dan tombol unduh file Excel-nya!";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                ],
            ],
        ];

        if (!empty($messageId)) {
            $this->telegramService->editMessageText($text, $this->getBotToken(), $chatId, $messageId, $keyboard);
            return;
        }

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $keyboard);
    }

    /**
     * Laporan Detail Laba Bersih & Analitik Operasional.
     */
    public function sendProfitDetailReport(string $chatId, ?int $messageId = null): void
    {
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00');
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $monthName = Carbon::now()->translatedFormat('F Y');

        $transactions = Transaction::whereBetween('created_at', [$startOfMonth, $now])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $txIds = $transactions->pluck('id');

        $grossProfit = (float) TransactionDetail::whereIn('transaction_id', $txIds)->sum('profit');
        $totalCostOfGoods = $totalRevenue - $grossProfit;

        $cashOut = (float) CashMovement::where('type', 'out')
            ->whereBetween('movement_date', [$startOfMonth, $now])
            ->sum('amount');

        $cashInExtra = (float) CashMovement::where('type', 'in')
            ->whereBetween('movement_date', [$startOfMonth, $now])
            ->sum('amount');

        $netProfit = $grossProfit - $cashOut;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        $text = "💡 <b>ANALISIS LABA & KEUANGAN (BULAN BERJALAN)</b>\n";
        $text .= "📅 <b>{$monthName}</b>\n";
        $text .= "────────────────────────────\n";
        $text .= "💰 <b>Omset Penjualan  : Rp " . number_format($totalRevenue, 0, ',', '.') . "</b>\n";
        $text .= "📦 Beban HPP Bahan    : <code>Rp " . number_format($totalCostOfGoods, 0, ',', '.') . "</code>\n";
        $text .= "──────────────────────────── ( - )\n";
        $text .= "📈 <b>Gross Margin     : Rp " . number_format($grossProfit, 0, ',', '.') . "</b>\n";
        $text .= "💸 Kas Keluar Kasir   : <code>Rp " . number_format($cashOut, 0, ',', '.') . "</code>\n";
        if ($cashInExtra > 0) {
            $text .= "📥 Kas Masuk Tambahan : <code>Rp " . number_format($cashInExtra, 0, ',', '.') . "</code>\n";
        }
        $text .= "──────────────────────────── ( - )\n";
        $profitEmoji = $netProfit >= 0 ? '🟢' : '🔴';
        $text .= "🏆 <b>NET PROFIT BERSIH : Rp " . number_format($netProfit, 0, ',', '.') . "</b> {$profitEmoji}\n";
        $text .= "📊 Margin Bersih      : <b>{$profitMargin}%</b>\n";
        $text .= "────────────────────────────";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📥 Unduh Excel Bulan Ini', 'callback_data' => 'owner_dl_excel:' . Carbon::now()->startOfMonth()->format('Y-m-d') . ':' . Carbon::today()->format('Y-m-d') . ":Bulan {$monthName}"],
                ],
                [
                    ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                ],
            ],
        ];

        if (!empty($messageId)) {
            $this->telegramService->editMessageText($text, $this->getBotToken(), $chatId, $messageId, $keyboard);
            return;
        }

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $keyboard);
    }

    /**
     * Panduan Perintah Bot Owner.
     */
    public function sendHelp(string $chatId, ?int $messageId = null): void
    {
        $text = "📖 <b>PANDUAN BOT ASISTEN OWNER</b>\n";
        $text .= "────────────────────────────\n";
        $text .= "Gunakan tombol menu di bawah keyboard untuk navigasi instan:\n\n";
        $text .= "• <b>⚡ Rekap Hari Ini</b> : Omset, profit, & cup hari ini\n";
        $text .= "• <b>⏮️ Rekap Kemarin</b> : Evaluasi performa kemarin\n";
        $text .= "• <b>📆 7 Hari Terakhir</b> : Tren performa 1 minggu\n";
        $text .= "• <b>🗓️ Rekap Bulan Ini</b> : Akumulasi bulan berjalan\n";
        $text .= "• <b>📥 Download Excel</b> : Kirim file spreadsheet .xlsx ke chat\n";
        $text .= "• <b>💡 Laba & Performa</b> : Analisis laba kotor, HPP, & laba bersih\n";
        $text .= "• <b>🔍 Custom Tanggal</b> : Ketik rentang bebas, contoh: <code>01/10/2026 - 15/10/2026</code>\n";
        $text .= "────────────────────────────";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                ],
            ],
        ];

        if (!empty($messageId)) {
            $this->telegramService->editMessageText($text, $this->getBotToken(), $chatId, $messageId, $keyboard);
            return;
        }

        $this->telegramService->sendMessage($text, $this->getBotToken(), $chatId, $keyboard);
    }

    /**
     * Kirim notifikasi audit closing shift kasir & rekonsiliasi kas laci langsung ke Bot Owner.
     * Fitur Loss Prevention: Mendeteksi selisih uang fisik vs sistem secara instan.
     */
    public function sendExecutiveShiftClosingAlert(CashierShift $shift): void
    {
        $botToken = $this->getBotToken();
        $chatId = $this->getChatId();

        if (empty($botToken) || empty($chatId)) {
            return;
        }

        try {
            $shift->loadMissing(['user', 'transactions.details.product.category', 'cashMovements.category']);

            $setting = $this->telegramService->getSetting();
            $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));
            $cashier = htmlspecialchars($shift->user?->name ?? 'Kasir');

            $startDt = Carbon::parse($shift->start_time);
            $endDt = $shift->end_time ? Carbon::parse($shift->end_time) : Carbon::now();
            $shiftDate = $startDt->format('Y-m-d');
            $shiftDateDisplay = $startDt->translatedFormat('d M Y');
            $startTime = $startDt->format('H:i');
            $endTime = $endDt->format('H:i');

            // Hitung durasi kerja shift
            $durationMinutes = $startDt->diffInMinutes($endDt);
            $hours = intdiv($durationMinutes, 60);
            $mins = $durationMinutes % 60;
            $durationStr = ($hours > 0 ? "{$hours} jam " : "") . "{$mins} menit";

            // Angka Finansial
            $totalSales = (float) $shift->total_sales;
            $cashSales = (float) $shift->cash_sales;
            $qrisSales = (float) $shift->qris_sales;
            $transferSales = (float) $shift->transfer_sales;
            $nonCashSales = $qrisSales + $transferSales;
            $totalTx = (int) ($shift->total_transactions ?? $shift->transactions()->count());

            $startingCash = (float) $shift->starting_cash;
            $totalCashIn = (float) ($shift->total_cash_in ?? 0);
            $totalCashOut = (float) ($shift->total_cash_out ?? 0);
            $expectedCash = (float) $shift->expected_cash;
            $actualCash = !is_null($shift->actual_cash) ? (float) $shift->actual_cash : null;
            $difference = !is_null($shift->difference) ? (float) $shift->difference : (!is_null($actualCash) ? ($actualCash - $expectedCash) : 0);

            // Format Status Selisih Laci (Loss Prevention)
            if (is_null($actualCash)) {
                $statusBanner = "⚠️ <b>AUDIT CLOSING SHIFT KASIR (FISIK BELUM DIINPUT)</b>";
                $diffSection = "⚖️ <b>STATUS LACI:</b> <i>Uang fisik belum dihitung oleh kasir.</i>\n";
            } elseif ($difference == 0) {
                $statusBanner = "🛡️ <b>AUDIT CLOSING SHIFT • LACI SESUAI</b>";
                $diffSection = "✅ <b>STATUS LACI: BALANCE / PAS (Rp 0)</b>\n"
                    . "<i>Uang fisik di laci cocok 100% dengan transaksi sistem.</i>\n";
            } elseif ($difference < 0) {
                $diffNominal = number_format(abs($difference), 0, ',', '.');
                $statusBanner = "🚨 <b>ALERT LOSS PREVENTION: KAS MINUS!</b>";
                $diffSection = "🔴 <b>PERINGATAN: KAS TEKOR / MINUS Rp {$diffNominal}!</b>\n"
                    . "⚠️ <i>Uang fisik kasir KURANG dari sistem. Waspada potensi salah kembalian atau kehilangan dana!</i>\n";
            } else {
                $diffNominal = number_format($difference, 0, ',', '.');
                $statusBanner = "🟡 <b>AUDIT CLOSING SHIFT • KAS LEBIH</b>";
                $diffSection = "🟡 <b>PERINGATAN: KAS LEBIH +Rp {$diffNominal}</b>\n"
                    . "ℹ️ <i>Uang fisik kasir LEBIH dari sistem. Kemungkinan ada transaksi tunai belum terinput POS.</i>\n";
            }

            // Susun Pesan Executive
            $text = "{$statusBanner}\n";
            $text .= "🏪 <b>{$shopName}</b>\n";
            $text .= "────────────────────────────\n";
            $text .= "👤 <b>Kasir:</b> {$cashier}\n";
            $text .= "🕒 <b>Waktu:</b> {$shiftDateDisplay} • {$startTime} - {$endTime} WIB ({$durationStr})\n";
            $text .= "🧾 <b>Total Nota:</b> {$totalTx} Transaksi Selesai\n";
            $text .= "────────────────────────────\n";

            $text .= "💵 <b>AUDIT SALDO LACI KASIR:</b>\n";
            $text .= "• Modal Awal Kasir   : Rp " . number_format($startingCash, 0, ',', '.') . "\n";
            $text .= "• Penjualan Tunai    : +Rp " . number_format($cashSales, 0, ',', '.') . "\n";
            if ($totalCashIn > 0) {
                $text .= "• Kas Masuk Operasional: +Rp " . number_format($totalCashIn, 0, ',', '.') . "\n";
            }
            if ($totalCashOut > 0) {
                $text .= "• Kas Keluar Operasional: -Rp " . number_format($totalCashOut, 0, ',', '.') . "\n";
            }
            $text .= "──────────────────────────── ( = )\n";
            $text .= "🎯 <b>Kas Laci Sistem  : Rp " . number_format($expectedCash, 0, ',', '.') . "</b>\n";
            if (!is_null($actualCash)) {
                $text .= "💵 <b>Uang Fisik Dihitung: Rp " . number_format($actualCash, 0, ',', '.') . "</b>\n";
            }
            $text .= "────────────────────────────\n";
            $text .= $diffSection;
            $text .= "────────────────────────────\n";

            // Breakdown Penjualan
            $cashPct = $totalSales > 0 ? round(($cashSales / $totalSales) * 100) : 0;
            $nonCashPct = $totalSales > 0 ? round(($nonCashSales / $totalSales) * 100) : 0;

            $text .= "💳 <b>RINGKASAN METODE PEMBAYARAN:</b>\n";
            $text .= "• Tunai       : Rp " . number_format($cashSales, 0, ',', '.') . " ({$cashPct}%)\n";
            $text .= "• Non-Tunai   : Rp " . number_format($nonCashSales, 0, ',', '.') . " ({$nonCashPct}%)\n";
            if ($qrisSales > 0 || $transferSales > 0) {
                $text .= "  ├ QRIS      : Rp " . number_format($qrisSales, 0, ',', '.') . "\n";
                $text .= "  └ Transfer  : Rp " . number_format($transferSales, 0, ',', '.') . "\n";
            }
            $text .= "💰 <b>TOTAL OMSET : Rp " . number_format($totalSales, 0, ',', '.') . "</b>\n";
            $text .= "────────────────────────────\n";

            // Catatan Kasir jika ada
            $notes = trim($shift->notes ?? '');
            if (!empty($notes)) {
                $notesEsc = htmlspecialchars($notes);
                $text .= "📝 <b>Catatan Kasir:</b>\n<i>\"{$notesEsc}\"</i>\n";
                $text .= "────────────────────────────\n";
            }

            $text .= "✅ <i>Laporan shift resmi tercatat di sistem pembukuan POS.</i>";

            // Tombol Interaktif
            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '📥 Unduh Excel Hari Ini', 'callback_data' => "owner_dl_excel:{$shiftDate}:{$shiftDate}"],
                        ['text' => '🏆 Menu Terlaris', 'callback_data' => "owner_top_range:{$shiftDate}:{$shiftDate}"],
                    ],
                    [
                        ['text' => '📊 Financial Dashboard', 'callback_data' => "owner_card:{$shiftDate}:{$shiftDate}"],
                        ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                    ],
                ],
            ];

            // Dispatch asinkron via queue agar tidak membebani kasir
            SendTelegramNotificationJob::dispatch(
                $text,
                $botToken,
                $chatId,
                $inlineKeyboard
            );
        } catch (\Throwable $e) {
            Log::error('sendExecutiveShiftClosingAlert Error: ' . $e->getMessage(), [
                'shift_id' => $shift->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Kirim notifikasi anti-fraud pembatalan nota (Void) langsung ke Bot Owner.
     */
    public function sendExecutiveVoidAlert(Transaction $transaction): void
    {
        $botToken = $this->getBotToken();
        $chatId = $this->getChatId();

        if (empty($botToken) || empty($chatId)) {
            return;
        }

        try {
            $transaction->loadMissing(['user', 'cancelledBy', 'details.product.category']);

            $setting = $this->telegramService->getSetting();
            $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));

            $invoice = htmlspecialchars($transaction->invoice_number);
            $cashier = htmlspecialchars($transaction->user?->name ?? 'Kasir');
            $cancelledBy = htmlspecialchars($transaction->cancelledBy?->name ?? (auth()->user()?->name ?? 'Staf POS'));
            $voidTime = Carbon::parse($transaction->cancelled_at ?? Carbon::now())->translatedFormat('d M Y, H:i') . ' WIB';
            $orderDate = Carbon::parse($transaction->created_at)->format('Y-m-d');

            $orderTypeRaw = str_replace('_', ' ', strtolower($transaction->order_type ?? 'dine in'));
            $orderType = ucwords($orderTypeRaw);
            if (!empty($transaction->table_number)) {
                $orderType .= ' (Meja ' . htmlspecialchars($transaction->table_number) . ')';
            }

            $totalVoid = number_format((float) $transaction->total, 0, ',', '.');
            $reason = htmlspecialchars($transaction->cancelled_reason ?: 'Tidak ada keterangan pembatalan');

            $text = "🚨 <b>ANTI-FRAUD ALERT: PEMBATALAN NOTA (VOID)</b>\n";
            $text .= "🏪 <b>{$shopName}</b>\n";
            $text .= "────────────────────────────\n";
            $text .= "🧾 <b>No. Nota     :</b> <code>#{$invoice}</code>\n";
            $text .= "🕒 <b>Waktu Batal  :</b> {$voidTime}\n";
            $text .= "👤 <b>Kasir Bertugas:</b> {$cashier}\n";
            $text .= "🚫 <b>Dibatalkan Oleh:</b> <b>{$cancelledBy}</b>\n";
            $text .= "📍 <b>Layanan      :</b> {$orderType}\n";
            $text .= "────────────────────────────\n";
            $text .= "💰 <b>NILAI TRANSAKSI BATAL: Rp {$totalVoid}</b>\n";
            $text .= "────────────────────────────\n";

            // Detail item yang dibatalkan
            $text .= "📋 <b>Item yang Dibatalkan:</b>\n";
            if ($transaction->details && $transaction->details->isNotEmpty()) {
                foreach ($transaction->details as $detail) {
                    $prodName = htmlspecialchars($detail->product?->name ?? 'Item');
                    $qty = (int) $detail->quantity;
                    $subtotal = number_format((float) $detail->subtotal, 0, ',', '.');
                    $text .= "▫️ {$qty}x <b>{$prodName}</b> — Rp {$subtotal}\n";

                    if (!empty($detail->addons) && is_array($detail->addons)) {
                        foreach ($detail->addons as $addon) {
                            $adName = htmlspecialchars($addon['name'] ?? '');
                            $adPrice = number_format((float) ($addon['price'] ?? 0), 0, ',', '.');
                            $text .= "   └ <i>+{$adName} (Rp {$adPrice})</i>\n";
                        }
                    }
                }
            } else {
                $text .= "<i>(Detail item tidak ditemukan)</i>\n";
            }

            $text .= "────────────────────────────\n";
            $text .= "📝 <b>Alasan Pembatalan:</b>\n";
            $text .= "<i>\"{$reason}\"</i>\n";
            $text .= "────────────────────────────\n";
            $text .= "🛡️ <i>Loss Prevention Alert: Pastikan pembatalan ini valid dan bukan penghapusan nota setelah pembayaran tunai diterima.</i>";

            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '📊 Rekap Finansial Hari Ini', 'callback_data' => "owner_card:{$orderDate}:{$orderDate}"],
                    ],
                    [
                        ['text' => '📥 Unduh Excel Hari Ini', 'callback_data' => "owner_dl_excel:{$orderDate}:{$orderDate}"],
                        ['text' => '🗑️ Tutup', 'callback_data' => 'owner_close'],
                    ],
                ],
            ];

            SendTelegramNotificationJob::dispatch(
                $text,
                $botToken,
                $chatId,
                $inlineKeyboard
            );
        } catch (\Throwable $e) {
            Log::error('sendExecutiveVoidAlert Error: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Cerdas membaca rentang tanggal dari teks bebas pengguna.
     * Mendukung format:
     * - DD/MM/YYYY - DD/MM/YYYY atau DD-MM-YYYY s/d DD-MM-YYYY
     * - YYYY-MM-DD - YYYY-MM-DD
     * - Nama bulan (misal: "rekap september 2026" atau "agustus")
     */
    public function parseDateRangeFromText(string $text): ?array
    {
        $clean = trim(strtolower($text));

        // 0. Kata Kunci Relatif: "hari ini", "kemarin", "7 hari", "bulan ini", "bulan lalu"
        if (str_contains($clean, 'hari ini') || str_contains($clean, 'today')) {
            $today = Carbon::today()->format('Y-m-d');
            return [
                'from' => $today,
                'to' => $today,
                'title' => 'Hari Ini (' . Carbon::now()->translatedFormat('d M Y') . ')',
            ];
        }

        if (str_contains($clean, 'kemarin') || str_contains($clean, 'yesterday')) {
            $yesterday = Carbon::yesterday()->format('Y-m-d');
            return [
                'from' => $yesterday,
                'to' => $yesterday,
                'title' => 'Kemarin (' . Carbon::yesterday()->translatedFormat('d M Y') . ')',
            ];
        }

        if (str_contains($clean, '7 hari') || str_contains($clean, 'minggu ini') || str_contains($clean, 'seminggu') || str_contains($clean, 'last 7 days')) {
            $start = Carbon::now()->subDays(6)->format('Y-m-d');
            $end = Carbon::today()->format('Y-m-d');
            return [
                'from' => $start,
                'to' => $end,
                'title' => '7 Hari Terakhir',
            ];
        }

        if (str_contains($clean, 'bulan ini') || str_contains($clean, 'this month')) {
            $start = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end = Carbon::today()->format('Y-m-d');
            return [
                'from' => $start,
                'to' => $end,
                'title' => 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')',
            ];
        }

        if (str_contains($clean, 'bulan lalu') || str_contains($clean, 'last month')) {
            $start = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $end = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
            return [
                'from' => $start,
                'to' => $end,
                'title' => 'Bulan Lalu (' . Carbon::now()->subMonth()->translatedFormat('F Y') . ')',
            ];
        }

        $monthMap = [
            'januari' => 1, 'jan' => 1,
            'februari' => 2, 'feb' => 2,
            'maret' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6,
            'juli' => 7, 'jul' => 7,
            'agustus' => 8, 'agu' => 8, 'agt' => 8,
            'september' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12,
        ];
        $monthNamesPattern = implode('|', array_keys($monthMap));
        $currentYear = (int) Carbon::now()->format('Y');

        // 1. Format: "1 oktober - 2 oktober" atau "1 okt 2026 s/d 2 okt 2026"
        if (preg_match('/(\d{1,2})\s+(' . $monthNamesPattern . ')\s*(?:(\d{4}))?\s*(?:-|s\/d|sd|sampai|to)\s*(\d{1,2})\s+(' . $monthNamesPattern . ')\s*(?:(\d{4}))?/i', $clean, $m)) {
            $m1 = $monthMap[strtolower($m[2])] ?? null;
            $m2 = $monthMap[strtolower($m[5])] ?? null;
            if ($m1 && $m2) {
                $y1 = !empty($m[3]) ? (int) $m[3] : (!empty($m[6]) ? (int) $m[6] : $currentYear);
                $y2 = !empty($m[6]) ? (int) $m[6] : $y1;
                $d1 = (int) $m[1];
                $d2 = (int) $m[4];

                $from = Carbon::createFromDate($y1, $m1, $d1)->format('Y-m-d');
                $to = Carbon::createFromDate($y2, $m2, $d2)->format('Y-m-d');
                return [
                    'from' => $from,
                    'to' => $to,
                    'title' => Carbon::parse($from)->translatedFormat('d M Y') . ' - ' . Carbon::parse($to)->translatedFormat('d M Y'),
                ];
            }
        }

        // 2. Format: "1 - 2 oktober" atau "1 s/d 5 oktober 2026"
        if (preg_match('/(\d{1,2})\s*(?:-|s\/d|sd|sampai|to)\s*(\d{1,2})\s+(' . $monthNamesPattern . ')\s*(?:(\d{4}))?/i', $clean, $m)) {
            $mNum = $monthMap[strtolower($m[3])] ?? null;
            if ($mNum) {
                $year = !empty($m[4]) ? (int) $m[4] : $currentYear;
                $d1 = (int) $m[1];
                $d2 = (int) $m[2];

                $from = Carbon::createFromDate($year, $mNum, $d1)->format('Y-m-d');
                $to = Carbon::createFromDate($year, $mNum, $d2)->format('Y-m-d');
                return [
                    'from' => $from,
                    'to' => $to,
                    'title' => Carbon::parse($from)->translatedFormat('d M Y') . ' - ' . Carbon::parse($to)->translatedFormat('d M Y'),
                ];
            }
        }

        // 3. Format: DD/MM/YYYY s/d DD/MM/YYYY
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\s*(?:-|s\/d|sd|sampai|to)\s*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $clean, $m)) {
            $from = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            $to = sprintf('%04d-%02d-%02d', (int) $m[6], (int) $m[5], (int) $m[4]);
            return [
                'from' => $from,
                'to' => $to,
                'title' => sprintf('%02d/%02d/%04d - %02d/%02d/%04d', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]),
            ];
        }

        // 4. Format: DD/MM s/d DD/MM (tanpa tahun, misal "01/10 - 05/10")
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})\s*(?:-|s\/d|sd|sampai|to)\s*(\d{1,2})[\/\-](\d{1,2})/', $clean, $m)) {
            $from = sprintf('%04d-%02d-%02d', $currentYear, (int) $m[2], (int) $m[1]);
            $to = sprintf('%04d-%02d-%02d', $currentYear, (int) $m[4], (int) $m[3]);
            return [
                'from' => $from,
                'to' => $to,
                'title' => Carbon::parse($from)->translatedFormat('d M Y') . ' - ' . Carbon::parse($to)->translatedFormat('d M Y'),
            ];
        }

        // 5. Format: YYYY-MM-DD s/d YYYY-MM-DD
        if (preg_match('/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})\s*(?:-|s\/d|sd|sampai|to)\s*(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/', $clean, $m)) {
            $from = sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
            $to = sprintf('%04d-%02d-%02d', (int) $m[4], (int) $m[5], (int) $m[6]);
            return [
                'from' => $from,
                'to' => $to,
                'title' => sprintf('%s s/d %s', $from, $to),
            ];
        }

        // 6. Format: Tanggal Tunggal dengan nama bulan, misal "1 oktober", "1 okt 2026", "rekap 2 oktober"
        if (preg_match('/(?:rekap\s+|tgl\s+|tanggal\s+)?(\d{1,2})\s+(' . $monthNamesPattern . ')\s*(?:(\d{4}))?/i', $clean, $m)) {
            $mNum = $monthMap[strtolower($m[2])] ?? null;
            if ($mNum) {
                $year = !empty($m[3]) ? (int) $m[3] : $currentYear;
                $d = (int) $m[1];
                $dt = Carbon::createFromDate($year, $mNum, $d)->format('Y-m-d');
                return [
                    'from' => $dt,
                    'to' => $dt,
                    'title' => Carbon::parse($dt)->translatedFormat('d F Y'),
                ];
            }
        }

        // 7. Format: Tanggal Tunggal numerik, misal "01/10/2026" atau "1/10/2026"
        if (preg_match('/(?:rekap\s+|tgl\s+|tanggal\s+)?(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $clean, $m)) {
            $dt = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            return [
                'from' => $dt,
                'to' => $dt,
                'title' => Carbon::parse($dt)->translatedFormat('d F Y'),
            ];
        }

        // 8. Format: 1 Bulan Penuh, misal "rekap september", "agustus 2026"
        foreach ($monthMap as $mName => $mNum) {
            if (preg_match('/\b' . $mName . '\b/i', $clean)) {
                $year = $currentYear;
                if (preg_match('/20\d{2}/', $clean, $yMatch)) {
                    $year = (int) $yMatch[0];
                }

                $cDate = Carbon::createFromDate($year, $mNum, 1);
                return [
                    'from' => $cDate->copy()->startOfMonth()->format('Y-m-d'),
                    'to' => $cDate->copy()->endOfMonth()->format('Y-m-d'),
                    'title' => $cDate->translatedFormat('F Y'),
                ];
            }
        }

        // 9. Format: "1 - 2" atau "tgl 1 - 2" (tanggal di bulan ini tanpa nama bulan)
        if (preg_match('/(?:tgl\s+|tanggal\s+)?(\b\d{1,2})\s*(?:-|s\/d|sd|sampai|to)\s*(\d{1,2}\b)(?!\s*[\/\-\w])/i', $clean, $m)) {
            $d1 = (int) $m[1];
            $d2 = (int) $m[2];
            if ($d1 >= 1 && $d1 <= 31 && $d2 >= 1 && $d2 <= 31 && $d1 <= $d2) {
                $currentMonth = (int) Carbon::now()->format('m');
                $from = Carbon::createFromDate($currentYear, $currentMonth, $d1)->format('Y-m-d');
                $to = Carbon::createFromDate($currentYear, $currentMonth, $d2)->format('Y-m-d');
                return [
                    'from' => $from,
                    'to' => $to,
                    'title' => Carbon::parse($from)->translatedFormat('d M Y') . ' - ' . Carbon::parse($to)->translatedFormat('d M Y'),
                ];
            }
        }

        return null;
    }

    /**
     * Konfigurasi ReplyKeyboardMarkup yang nempel di bawah field chat secara persisten.
     */
    public function getPersistentKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '⚡ Rekap Hari Ini'],
                    ['text' => '⏮️ Rekap Kemarin'],
                ],
                [
                    ['text' => '📆 7 Hari Terakhir'],
                    ['text' => '🗓️ Rekap Bulan Ini'],
                ],
                [
                    ['text' => '🏆 Menu Terlaris'],
                    ['text' => '📥 Download Excel'],
                ],
            ],
            'resize_keyboard' => true,
            'is_persistent' => true,
            'input_field_placeholder' => 'Ketik misal: 1 - 2 okt atau menu 1 - 2 okt',
        ];
    }

    /**
     * Helper visual progress bar (misal: ▓▓▓▓▓▓▓░░░ 70%).
     */
    protected function generateProgressBar(float $percentage, int $totalChars = 8): string
    {
        $clamped = max(0, min(100, $percentage));
        $filledChars = (int) round(($clamped / 100) * $totalChars);
        $emptyChars = max(0, $totalChars - $filledChars);

        return str_repeat('▓', $filledChars) . str_repeat('░', $emptyChars);
    }
}
