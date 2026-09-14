<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashierShift;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramBotCommandService
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Entry point untuk memproses payload Webhook Telegram.
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
            Log::error('TelegramBotCommandService Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle pesan teks / slash command yang masuk.
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

        // Cek Otorisasi Keamanan
        if (!$this->isAuthorized($chatId, $userId)) {
            $this->sendUnauthorizedReply($chatId);
            return;
        }

        // Bersihkan command dari mention bot, misal "/omset@NamaBot" -> "/omset"
        $command = strtolower(explode(' ', $text)[0]);
        if (str_contains($command, '@')) {
            $command = explode('@', $command)[0];
        }

        switch ($command) {
            case '/start':
            case '/menu':
            case 'menu':
                $this->sendMainMenu($chatId, $fromName);
                break;

            case '/omset':
            case 'omset':
                $this->sendOmsetReport($chatId);
                break;

            case '/laris':
            case 'laris':
                $this->sendTopMenuReport($chatId);
                break;

            case '/cup':
            case 'cup':
                $this->sendCupSalesReport($chatId);
                break;

            case '/shift':
            case 'shift':
                $this->sendShiftReport($chatId);
                break;

            case '/pengeluaran':
            case '/biaya':
            case 'pengeluaran':
                $this->sendExpenseReport($chatId);
                break;

            case '/kemarin':
            case 'kemarin':
                $this->sendYesterdayReport($chatId);
                break;

            case '/void':
            case '/batal':
            case 'void':
            case 'batal':
                $param = trim(substr($text, strlen($command)));
                $this->handleVoidCommand($chatId, $param);
                break;

            case '/bulan':
            case 'bulan':
                $this->sendMonthReport($chatId);
                break;

            case '/bantuan':
            case '/help':
            case 'help':
                $this->sendHelp($chatId);
                break;

            default:
                // Jika pesan tidak dikenal dan diawali slash '/', beri menu bantuan
                if (str_starts_with($command, '/')) {
                    $this->sendMainMenu($chatId, $fromName, "Perintah <code>{$command}</code> tidak dikenali.");
                }
                break;
        }
    }

    /**
     * Handle event klik tombol inline (callback query).
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $chatId = (string) ($callbackQuery['message']['chat']['id'] ?? '');
        $messageId = (int) ($callbackQuery['message']['message_id'] ?? 0);
        $userId = (string) ($callbackQuery['from']['id'] ?? '');
        $action = trim($callbackQuery['data'] ?? '');
        $fromName = trim(($callbackQuery['from']['first_name'] ?? '') . ' ' . ($callbackQuery['from']['last_name'] ?? ''));

        if (empty($chatId) || empty($action)) {
            return;
        }

        // Cek Otorisasi Keamanan
        if (!$this->isAuthorized($chatId, $userId)) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Akses ditolak.', true);
            $this->sendUnauthorizedReply($chatId);
            return;
        }

        // Aksi Tutup Pesan (Hapus bubble pesan dari chat agar bersih)
        if ($action === 'cmd_close') {
            $this->telegramService->answerCallbackQuery($callbackId, 'Pesan ditutup');
            if ($messageId > 0) {
                $this->telegramService->deleteMessage(null, $chatId, $messageId);
            }
            return;
        }

        // === Handler Flow Void / Pembatalan Nota (Flow A & Flow B) ===
        if (str_starts_with($action, 'void_req_')) {
            $txId = (int) substr($action, strlen('void_req_'));
            $this->handleVoidRequest($chatId, $txId, $messageId, $callbackId, $fromName);
            return;
        }

        if (str_starts_with($action, 'void_cancel_')) {
            $txId = (int) substr($action, strlen('void_cancel_'));
            $this->handleVoidCancel($chatId, $txId, $messageId, $callbackId);
            return;
        }

        if (str_starts_with($action, 'void_do_')) {
            $payload = substr($action, strlen('void_do_'));
            $parts = explode('_', $payload, 2);
            $txId = (int) ($parts[0] ?? 0);
            $reasonKey = $parts[1] ?? 'lainnya';
            $this->handleVoidExecution($chatId, $txId, $reasonKey, $messageId, $callbackId, $fromName);
            return;
        }

        if ($action === 'menu_void_list') {
            $this->sendActiveTransactionsForVoid($chatId, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId);
            return;
        }

        // Jalankan perintah laporan secepat kilat
        switch ($action) {
            case 'menu_main':
                $this->sendMainMenu($chatId, $fromName, null, $messageId);
                break;

            case 'cmd_omset':
                $this->sendOmsetReport($chatId, $messageId);
                break;

            case 'cmd_laris':
                $this->sendTopMenuReport($chatId, $messageId);
                break;

            case 'cmd_cup':
                $this->sendCupSalesReport($chatId, $messageId);
                break;

            case 'cmd_shift':
                $this->sendShiftReport($chatId, $messageId);
                break;

            case 'cmd_pengeluaran':
                $this->sendExpenseReport($chatId, $messageId);
                break;

            case 'cmd_kemarin':
                $this->sendYesterdayReport($chatId, $messageId);
                break;

            case 'cmd_void':
                $this->sendVoidReport($chatId, $messageId);
                break;

            case 'cmd_bulan':
                $this->sendMonthReport($chatId, $messageId);
                break;

            default:
                $this->sendMainMenu($chatId, $fromName, null, $messageId);
                break;
        }

        // Matikan indikator loading pada tombol Telegram
        $this->telegramService->answerCallbackQuery($callbackId);
    }

    /**
     * Kirim balasan secepat kilat (Instant In-Place Edit):
     * - Langsung mengedit teks pesan di tempat tanpa delay dan tanpa multi-request (hanya 1x HTTP request).
     * - Chat tetap bersih (hanya 1 bubble aktif), respon instan dalam hitungan milidetik.
     * - Fallback kirim baru jika pesan terhapus atau berupa command teks manual.
     */
    protected function deliverResponse(string $chatId, string $text, array $keyboard = [], ?int $messageId = null): array
    {
        if (!empty($messageId)) {
            $editResult = $this->telegramService->editMessageText($text, null, $chatId, $messageId, $keyboard);
            if (($editResult['success'] ?? false) || ($editResult['not_modified'] ?? false)) {
                return $editResult;
            }
        }

        return $this->telegramService->sendMessage($text, null, $chatId, $keyboard);
    }

    /**
     * Cek apakah chat ID atau user ID memiliki izin mengakses data.
     */
    public function isAuthorized(string $chatId, ?string $userId = null): bool
    {
        $setting = $this->telegramService->getSetting();
        $configuredChatId = trim($this->telegramService->getChatId($setting) ?? '');

        // 1. Jika Chat ID sama persis dengan yang tersimpan di database/env (Grup atau Japri)
        if (!empty($configuredChatId) && $chatId === $configuredChatId) {
            return true;
        }

        // 2. Jika pengirim (User ID) sama dengan yang tersimpan
        if (!empty($configuredChatId) && !empty($userId) && $userId === $configuredChatId) {
            return true;
        }

        // 3. Cek daftar admin ID tambahan di .env (opsional: TELEGRAM_ADMIN_IDS=12345,67890)
        $adminIdsRaw = config('services.telegram.admin_ids', '');
        if (!empty($adminIdsRaw)) {
            $allowedList = array_map('trim', explode(',', $adminIdsRaw));
            if (in_array($chatId, $allowedList) || (!empty($userId) && in_array($userId, $allowedList))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Balasan jika akses ditolak (bukan admin terdaftar).
     */
    protected function sendUnauthorizedReply(string $chatId): void
    {
        $text = "⛔ <b>Akses Ditolak</b>\n";
        $text .= "Akun/Grup Anda tidak memiliki izin untuk mengakses data keuangan POS Cafe ini.\n\n";
        $text .= "🆔 <b>ID Anda:</b> <code>{$chatId}</code>\n";
        $text .= "<i>(Beri tahu administrator untuk mendaftarkan ID ini di pengaturan jika Anda adalah pemilik).</i>";

        $this->telegramService->sendMessage($text, null, $chatId);
    }

    /**
     * Tampilkan Menu Utama interaktif (Inline Keyboard).
     */
    public function sendMainMenu(string $chatId, string $fromName = '', ?string $notice = null, ?int $messageId = null): void
    {
        $setting = $this->telegramService->getSetting();
        $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));
        $nowFormatted = Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB';

        $text = "🏪 <b>{$shopName} — DASHBOARD</b>\n";
        $text .= "🕒 {$nowFormatted}\n";
        $text .= "────────────────────\n";
        if ($notice) {
            $text .= "ℹ️ <i>{$notice}</i>\n\n";
        }
        $text .= "Pilih modul laporan operasional:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💰 Omset Hari Ini', 'callback_data' => 'cmd_omset'],
                    ['text' => '🏆 Menu Terlaris', 'callback_data' => 'cmd_laris'],
                ],
                [
                    ['text' => '🥤 Total Cup', 'callback_data' => 'cmd_cup'],
                    ['text' => '👤 Status Shift', 'callback_data' => 'cmd_shift'],
                ],
                [
                    ['text' => '💸 Kas Keluar', 'callback_data' => 'cmd_pengeluaran'],
                    ['text' => '📅 Rekap Kemarin', 'callback_data' => 'cmd_kemarin'],
                ],
                [
                    ['text' => '🚫 Log Void', 'callback_data' => 'cmd_void'],
                    ['text' => '❌ Batalkan Nota', 'callback_data' => 'menu_void_list'],
                ],
                [
                    ['text' => '📆 Rekap Bulan Ini', 'callback_data' => 'cmd_bulan'],
                    ['text' => '🔄 Refresh Menu', 'callback_data' => 'menu_main'],
                ],
                [
                    ['text' => '🗑️ Tutup Menu', 'callback_data' => 'cmd_close'],
                ],
            ],
        ];

        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 1. Laporan Omset Hari Ini.
     */
    public function sendOmsetReport(string $chatId, ?int $messageId = null): void
    {
        $today = Carbon::today();
        $nowFormatted = Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB';

        // Ambil transaksi yang benar-benar LUNAS
        $transactions = Transaction::whereDate('created_at', $today)
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

        $totalOmset = (float) $transactions->sum('total');
        $totalTrx = $transactions->count();
        $avgBasket = $totalTrx > 0 ? ($totalOmset / $totalTrx) : 0;

        // Breakdown Metode Pembayaran
        $cashSales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'cash')->sum('total');
        $cashCount = $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'cash')->count();

        $qrisSales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'qris')->sum('total');
        $qrisCount = $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'qris')->count();

        $transferSales = (float) $transactions->filter(fn($t) => in_array(strtolower($t->payment_method ?? ''), ['transfer', 'debit']))->sum('total');
        $transferCount = $transactions->filter(fn($t) => in_array(strtolower($t->payment_method ?? ''), ['transfer', 'debit']))->count();

        // Cek Open Bill aktif hari ini
        $openBills = Transaction::whereDate('created_at', $today)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->where('payment_status', 'unpaid')
                  ->orWhere('paid', '<=', 0);
            })
            ->get();
        $openBillsCount = $openBills->count();
        $openBillsTotal = (float) $openBills->sum('total');

        $totalCupsToday = (int) TransactionDetail::whereIn('transaction_id', $transactions->pluck('id'))->sum('quantity');

        $text = "📊 <b>RINGKASAN OMSET HARI INI</b>\n";
        $text .= "🕒 {$nowFormatted}\n";
        $text .= "────────────────────\n";
        $text .= "💰 <b>Total Omset : Rp " . number_format($totalOmset, 0, ',', '.') . "</b>\n";
        $text .= "🧾 <b>Total Trx   : {$totalTrx} transaksi</b>\n";
        $text .= "🥤 <b>Total Cup   : {$totalCupsToday} cup terjual</b>\n";
        $text .= "👥 <b>Rata-rata   : Rp " . number_format($avgBasket, 0, ',', '.') . " / trx</b>\n\n";

        $text .= "💳 <b>Metode Pembayaran:</b>\n";
        $text .= "• Tunai (Cash) : Rp " . number_format($cashSales, 0, ',', '.') . " ({$cashCount} trx)\n";
        $text .= "• QRIS         : Rp " . number_format($qrisSales, 0, ',', '.') . " ({$qrisCount} trx)\n";
        if ($transferCount > 0) {
            $text .= "• Transfer     : Rp " . number_format($transferSales, 0, ',', '.') . " ({$transferCount} trx)\n";
        }

        if ($openBillsCount > 0) {
            $text .= "\n⏳ <b>Open Bill Aktif:</b> {$openBillsCount} pesanan (Rp " . number_format($openBillsTotal, 0, ',', '.') . ")\n";
        }
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_omset');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 2. Laporan 5 Menu Terlaris Hari Ini.
     */
    public function sendTopMenuReport(string $chatId, ?int $messageId = null): void
    {
        $today = Carbon::today();
        $dateFormatted = Carbon::now()->translatedFormat('d M Y');

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
            ->whereDate('transactions.created_at', $today)
            ->where(function ($q) {
                $q->where('transactions.status', 'completed')
                  ->orWhere(function ($sq) {
                      $sq->where('transactions.order_source', 'self_order')
                         ->where('transactions.payment_status', 'paid')
                         ->whereNotIn('transactions.status', ['cancelled', 'pending']);
                  });
            })
            ->where('transactions.status', '!=', 'cancelled')
            ->where('transactions.payment_status', '!=', 'unpaid')
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $text = "🏆 <b>TOP 5 MENU TERLARIS HARI INI</b>\n";
        $text .= "📅 {$dateFormatted}\n";
        $text .= "────────────────────\n";

        if ($topProducts->isEmpty()) {
            $text .= "<i>Belum ada penjualan selesai hari ini.</i>\n";
        } else {
            foreach ($topProducts as $index => $item) {
                $rank = $index + 1;
                $name = htmlspecialchars($item->name ?? 'Menu');
                $qty = (int) $item->total_sold;
                $unit = $this->telegramService->determineUnit($item->name, $item->category_name);
                $nominal = number_format((float) $item->total_nominal, 0, ',', '.');

                $text .= "<b>{$rank}. {$name}</b>\n";
                $text .= "   └ Terjual: <b>{$qty} {$unit}</b> • Rp {$nominal}\n";
            }
        }
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_laris');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 3. Laporan Total Cup & Kategori Minuman.
     */
    public function sendCupSalesReport(string $chatId, ?int $messageId = null): void
    {
        $today = Carbon::today();
        $dateFormatted = Carbon::now()->translatedFormat('d M Y');

        $details = TransactionDetail::select(
            'transaction_details.quantity',
            'products.name as product_name',
            'categories.name as category_name'
        )
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereDate('transactions.created_at', $today)
            ->where(function ($q) {
                $q->where('transactions.status', 'completed')
                  ->orWhere(function ($sq) {
                      $sq->where('transactions.order_source', 'self_order')
                         ->where('transactions.payment_status', 'paid')
                         ->whereNotIn('transactions.status', ['cancelled', 'pending']);
                  });
            })
            ->where('transactions.status', '!=', 'cancelled')
            ->where('transactions.payment_status', '!=', 'unpaid')
            ->get();

        $totalCups = 0;
        $categoryBreakdown = [];

        foreach ($details as $d) {
            $catName = $d->category_name ?: 'Menu Lainnya';
            $qty = (int) $d->quantity;

            $totalCups += $qty;
            if (!isset($categoryBreakdown[$catName])) {
                $categoryBreakdown[$catName] = 0;
            }
            $categoryBreakdown[$catName] += $qty;
        }

        // Urutkan kategori: Terlaris (cup terbanyak) di atas, jika jumlah sama urutkan sesuai abjad
        uksort($categoryBreakdown, function ($a, $b) use ($categoryBreakdown) {
            return ($categoryBreakdown[$b] <=> $categoryBreakdown[$a]) ?: strcasecmp($a, $b);
        });

        $text = "🥤 <b>TOTAL PENJUALAN CUP HARI INI</b>\n";
        $text .= "📅 {$dateFormatted}\n";
        $text .= "────────────────────\n";
        $text .= "🥤 <b>Total Cup Terjual : {$totalCups} cup</b>\n\n";

        if (!empty($categoryBreakdown)) {
            $text .= "🏷️ <b>Penjualan per Kategori (Terlaris):</b>\n";
            foreach ($categoryBreakdown as $catName => $qty) {
                $icon = $this->telegramService->getCategoryIcon($catName);
                $catEsc = htmlspecialchars($catName);
                $text .= "• {$icon} {$catEsc}: <b>{$qty} cup</b>\n";
            }
        } else {
            $text .= "<i>Belum ada penjualan cup/menu hari ini.</i>\n";
        }
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_cup');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 4. Status Shift Kasir & Laci Kasir Aktif.
     */
    public function sendShiftReport(string $chatId, ?int $messageId = null): void
    {
        $activeShift = CashierShift::where('status', 'open')->latest()->first();

        if (!$activeShift) {
            $text = "👤 <b>STATUS SHIFT KASIR</b>\n";
            $text .= "────────────────────\n";
            $text .= "<i>Tidak ada shift kasir yang sedang aktif saat ini.</i>\n";
            $text .= "────────────────────";

            $keyboard = $this->getActionKeyboard('cmd_shift');
            $this->deliverResponse($chatId, $text, $keyboard, $messageId);
            return;
        }

        $activeShift->loadMissing(['user']);
        $cashierName = htmlspecialchars($activeShift->user?->name ?? 'Kasir');
        $startTime = Carbon::parse($activeShift->start_time)->translatedFormat('d M Y, H:i');
        $duration = Carbon::parse($activeShift->start_time)->diffForHumans(null, true);

        // Recalculate untuk data paling fresh
        $activeShift->recalculateTotals();

        $startingCash = number_format((float) $activeShift->starting_cash, 0, ',', '.');
        $cashSales = number_format((float) $activeShift->cash_sales, 0, ',', '.');
        $qrisSales = number_format((float) $activeShift->qris_sales, 0, ',', '.');
        $transferSales = number_format((float) $activeShift->transfer_sales, 0, ',', '.');
        $totalSales = number_format((float) $activeShift->total_sales, 0, ',', '.');
        $cashIn = number_format((float) $activeShift->total_cash_in, 0, ',', '.');
        $cashOut = number_format((float) $activeShift->total_cash_out, 0, ',', '.');
        $expectedCash = number_format((float) $activeShift->expected_cash, 0, ',', '.');
        $trxCount = (int) $activeShift->total_transactions;
        $shiftCompletedTxIds = $activeShift->transactions()->where('status', 'completed')->pluck('id');
        $shiftTotalCups = (int) TransactionDetail::whereIn('transaction_id', $shiftCompletedTxIds)->sum('quantity');

        $text = "👤 <b>STATUS SHIFT KASIR AKTIF</b>\n";
        $text .= "────────────────────\n";
        $text .= "• Kasir Bertugas : <b>{$cashierName}</b>\n";
        $text .= "• Waktu Mulai    : {$startTime} WIB ({$duration})\n";
        $text .= "• Total Trx      : {$trxCount} transaksi\n";
        $text .= "• Total Cup      : {$shiftTotalCups} cup terjual\n";
        $text .= "• Omset Shift    : <b>Rp {$totalSales}</b>\n\n";

        $text .= "💵 <b>Posisi Laci Kasir (Cash):</b>\n";
        $text .= "• Modal Awal     : Rp {$startingCash}\n";
        $text .= "• Penjualan Tunai: +Rp {$cashSales}\n";
        if ((float) $activeShift->total_cash_in > 0) {
            $text .= "• Kas Masuk Laci : +Rp {$cashIn}\n";
        }
        if ((float) $activeShift->total_cash_out > 0) {
            $text .= "• Kas Keluar Laci: -Rp {$cashOut}\n";
        }
        $text .= "────────────────────\n";
        $text .= "📦 <b>Uang Fisik Laci : Rp {$expectedCash}</b>\n";
        $text .= "💳 <i>Non-Tunai: QRIS Rp {$qrisSales}" . ((float) $activeShift->transfer_sales > 0 ? " • Transfer Rp {$transferSales}" : "") . "</i>\n";
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_shift');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 5. Laporan Pengeluaran Kas / Biaya Operasional Hari Ini.
     */
    public function sendExpenseReport(string $chatId, ?int $messageId = null): void
    {
        $today = Carbon::today();
        $dateFormatted = Carbon::now()->translatedFormat('d M Y');

        $expenses = CashMovement::whereDate('created_at', $today)
            ->where('type', 'out')
            ->with(['category', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpense = (float) $expenses->sum('amount');

        $text = "💸 <b>PENGELUARAN KAS HARI INI</b>\n";
        $text .= "📅 {$dateFormatted}\n";
        $text .= "────────────────────\n";
        $text .= "📉 <b>Total Biaya Keluar : Rp " . number_format($totalExpense, 0, ',', '.') . "</b>\n\n";

        if ($expenses->isEmpty()) {
            $text .= "<i>Tidak ada pengeluaran kas tercatat hari ini.</i>\n";
        } else {
            $text .= "<b>Rincian Pengeluaran:</b>\n";
            foreach ($expenses as $item) {
                $time = Carbon::parse($item->created_at)->format('H:i');
                $catName = htmlspecialchars($item->category?->name ?? 'Operasional');
                $nominal = number_format((float) $item->amount, 0, ',', '.');
                $notes = htmlspecialchars($item->notes ?: $catName);
                $cashier = htmlspecialchars($item->user?->name ?? 'Kasir');

                $text .= "• [{$time}] <b>Rp {$nominal}</b>\n";
                $text .= "  └ <i>{$notes}</i> ({$cashier})\n";
            }
        }
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_pengeluaran');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 6. Komparasi Penjualan Kemarin vs Hari Ini.
     */
    public function sendYesterdayReport(string $chatId, ?int $messageId = null): void
    {
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        $yesterdayTx = Transaction::whereDate('created_at', $yesterday)->where('status', 'completed')->get();
        $yesterdayRevenue = (float) $yesterdayTx->sum('total');
        $yesterdayCount = $yesterdayTx->count();

        $todayTx = Transaction::whereDate('created_at', $today)->where('status', 'completed')->get();
        $todayRevenue = (float) $todayTx->sum('total');
        $todayCount = $todayTx->count();

        $diff = $todayRevenue - $yesterdayRevenue;
        $percent = $yesterdayRevenue > 0 ? round(($diff / $yesterdayRevenue) * 100, 1) : ($todayRevenue > 0 ? 100 : 0);
        $indicator = $diff >= 0 ? "🟢 Naik +{$percent}%" : "🔴 Turun {$percent}%";

        $text = "📅 <b>KOMPARASI PENJUALAN KEMARIN</b>\n";
        $text .= "────────────────────\n";
        $text .= "<b>Kemarin (" . $yesterday->translatedFormat('d M Y') . "):</b>\n";
        $text .= "• Omset : Rp " . number_format($yesterdayRevenue, 0, ',', '.') . "\n";
        $text .= "• Trx   : {$yesterdayCount} transaksi\n\n";

        $text .= "<b>Hari Ini (s/d saat ini):</b>\n";
        $text .= "• Omset : Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";
        $text .= "• Trx   : {$todayCount} transaksi\n";
        $text .= "• Tren  : <b>{$indicator}</b>\n";
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_kemarin');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 7. Log Transaksi yang Dibatalkan / Void Hari Ini.
     */
    public function sendVoidReport(string $chatId, ?int $messageId = null): void
    {
        $today = Carbon::today();
        $dateFormatted = Carbon::now()->translatedFormat('d M Y');

        $voidTransactions = Transaction::whereDate('created_at', $today)
            ->where('status', 'cancelled')
            ->with(['user', 'cancelledBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalVoid = (float) $voidTransactions->sum('total');
        $count = $voidTransactions->count();

        $text = "🚫 <b>LOG PEMBATALAN (VOID) HARI INI</b>\n";
        $text .= "📅 {$dateFormatted}\n";
        $text .= "────────────────────\n";

        if ($voidTransactions->isEmpty()) {
            $text .= "<i>Tidak ada transaksi yang dibatalkan hari ini.</i>\n";
        } else {
            $text .= "Total Dibatalkan: <b>{$count} trx (Rp " . number_format($totalVoid, 0, ',', '.') . ")</b>\n\n";
            foreach ($voidTransactions as $tx) {
                $invoice = htmlspecialchars($tx->invoice_number);
                $time = Carbon::parse($tx->cancelled_at ?: $tx->updated_at)->format('H:i');
                $nominal = number_format((float) $tx->total, 0, ',', '.');
                $reason = htmlspecialchars($tx->cancelled_reason ?: 'Tanpa keterangan');
                $byUser = htmlspecialchars($tx->cancelledBy?->name ?? ($tx->user?->name ?? 'Kasir'));

                $text .= "• <code>#{$invoice}</code> [{$time}] • <b>Rp {$nominal}</b>\n";
                $text .= "  └ <i>{$reason}</i> ({$byUser})\n";
            }
        }
        $text .= "────────────────────";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '❌ Batalkan Nota Hari Ini', 'callback_data' => 'menu_void_list'],
                    ['text' => '🔄 Refresh Log', 'callback_data' => 'cmd_void'],
                ],
                [
                    ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                    ['text' => '🗑️ Tutup Pesan', 'callback_data' => 'cmd_close'],
                ],
            ],
        ];
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * 8. Rekap Akumulasi Bulan Berjalan (Month to Date).
     */
    public function sendMonthReport(string $chatId, ?int $messageId = null): void
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $now = Carbon::now();
        $monthName = $now->translatedFormat('F Y');

        $transactions = Transaction::whereBetween('created_at', [$startOfMonth, $now])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $totalCount = $transactions->count();
        $daysPassed = max(1, (int) $now->format('d'));
        $dailyAvg = $totalRevenue / $daysPassed;

        $text = "📆 <b>REKAP PENJUALAN BULAN INI</b>\n";
        $text .= "📅 {$monthName} (Tgl 1 - " . $now->format('d') . ")\n";
        $text .= "────────────────────\n";
        $text .= "💰 <b>Total Omset : Rp " . number_format($totalRevenue, 0, ',', '.') . "</b>\n";
        $text .= "🧾 <b>Total Trx   : {$totalCount} transaksi</b>\n";
        $text .= "📈 <b>Rata-rata/Hari : Rp " . number_format($dailyAvg, 0, ',', '.') . "</b>\n";
        $text .= "────────────────────";

        $keyboard = $this->getActionKeyboard('cmd_bulan');
        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Menu Bantuan / Panduan Perintah.
     */
    public function sendHelp(string $chatId, ?int $messageId = null): void
    {
        $text = "📖 <b>PANDUAN PERINTAH BOT</b>\n";
        $text .= "────────────────────\n";
        $text .= "• /menu — Tampilkan menu tombol utama\n";
        $text .= "• /omset — Laporan omset & uang masuk hari ini\n";
        $text .= "• /laris — 5 Menu paling laris hari ini\n";
        $text .= "• /cup — Total cup minuman terjual hari ini\n";
        $text .= "• /shift — Status kasir & uang kas laci aktif\n";
        $text .= "• /pengeluaran — Catatan uang keluar / operasional\n";
        $text .= "• /kemarin — Komparasi omset kemarin vs hari ini\n";
        $text .= "• /void — Log pesanan yang dibatalkan kasir\n";
        $text .= "• /bulan — Rekapitulasi omset bulan berjalan\n";
        $text .= "────────────────────";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🏠 Buka Menu Utama', 'callback_data' => 'menu_main'],
                ],
                [
                    ['text' => '🗑️ Tutup Pesan', 'callback_data' => 'cmd_close'],
                ],
            ],
        ];

        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Helper untuk tombol navigasi di bawah setiap laporan (Refresh, Menu Utama, dan Tutup).
     */
    protected function getActionKeyboard(string $currentAction): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔄 Refresh Data', 'callback_data' => $currentAction],
                    ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                ],
                [
                    ['text' => '🗑️ Tutup Pesan', 'callback_data' => 'cmd_close'],
                ],
            ],
        ];
    }

    /**
     * Handler untuk command teks /void atau /batal.
     */
    public function handleVoidCommand(string $chatId, string $query = ''): void
    {
        if (!empty($query)) {
            $this->sendActiveTransactionsForVoid($chatId, null, $query);
        } else {
            $this->sendActiveTransactionsForVoid($chatId);
        }
    }

    /**
     * Tampilkan daftar transaksi hari ini yang dapat dibatalkan (Flow B).
     */
    public function sendActiveTransactionsForVoid(string $chatId, ?int $messageId = null, string $search = ''): void
    {
        $today = Carbon::today();
        $dateFormatted = Carbon::now()->translatedFormat('d M Y');

        $query = Transaction::whereDate('created_at', $today)
            ->where(function ($q) {
                $q->where('status', 'completed')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')
                         ->where('order_source', 'self_order')
                         ->where('payment_status', 'paid');
                  });
            })
            ->where('status', '!=', 'cancelled');

        if (!empty($search)) {
            $cleanSearch = ltrim($search, '#');
            $query->where('invoice_number', 'like', "%{$cleanSearch}%");
        }

        $activeTransactions = $query->orderBy('created_at', 'desc')->take(6)->get();

        $text = "🚫 <b>BATALKAN NOTA (VOID PESANAN)</b>\n";
        $text .= "📅 {$dateFormatted}\n";
        $text .= "────────────────────\n";

        if ($activeTransactions->isEmpty()) {
            if (!empty($search)) {
                $text .= "<i>Tidak ditemukan transaksi aktif dengan nota \"{$search}\" hari ini.</i>\n\n";
            } else {
                $text .= "<i>Tidak ada transaksi aktif yang dapat dibatalkan hari ini.</i>\n\n";
            }
            $text .= "<i>(Hanya transaksi yang belum dibatalkan yang bisa di-void).</i>\n";
            $text .= "────────────────────";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '📋 Lihat Log Void Hari Ini', 'callback_data' => 'cmd_void'],
                        ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                    ],
                    [
                        ['text' => '🗑️ Tutup', 'callback_data' => 'cmd_close'],
                    ],
                ],
            ];

            $this->deliverResponse($chatId, $text, $keyboard, $messageId);
            return;
        }

        $text .= "Pilih nota yang ingin dibatalkan:\n\n";
        $buttons = [];

        foreach ($activeTransactions as $tx) {
            $invoice = htmlspecialchars($tx->invoice_number);
            $nominal = number_format((float) $tx->total, 0, ',', '.');
            $time = Carbon::parse($tx->created_at)->format('H:i');
            $type = $tx->order_type ? ucwords(str_replace('_', ' ', $tx->order_type)) : 'Order';
            $cust = !empty($tx->customer_name) ? " • " . htmlspecialchars($tx->customer_name) : '';

            $text .= "• <code>#{$invoice}</code> [{$time}] Rp {$nominal} ({$type}{$cust})\n";

            $btnLabel = "❌ Void #{$invoice} (Rp {$nominal})";
            $buttons[] = [
                ['text' => $btnLabel, 'callback_data' => "void_req_{$tx->id}"],
            ];
        }

        $buttons[] = [
            ['text' => '📋 Log Void', 'callback_data' => 'cmd_void'],
            ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
        ];
        $buttons[] = [
            ['text' => '🗑️ Tutup Pesan', 'callback_data' => 'cmd_close'],
        ];

        $text .= "────────────────────";

        $this->deliverResponse($chatId, $text, ['inline_keyboard' => $buttons], $messageId);
    }

    /**
     * Tampilkan konfirmasi dan opsi alasan pembatalan nota (Flow A & B).
     */
    public function handleVoidRequest(string $chatId, int $txId, ?int $messageId, string $callbackId, string $fromName): void
    {
        $transaction = Transaction::with(['user', 'details.product'])->find($txId);

        if (!$transaction) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Nota tidak ditemukan di sistem.', true);
            return;
        }

        if ($transaction->status === 'cancelled') {
            $transaction->load(['cancelledBy', 'user', 'details.product.category', 'shift']);
            $setting = $this->telegramService->getSetting();
            $voidAlertText = $this->telegramService->formatVoidMessage($transaction, $setting);
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '📋 Log Void', 'callback_data' => 'cmd_void'],
                        ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                    ],
                ],
            ];
            $this->deliverResponse($chatId, $voidAlertText, $keyboard, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId, 'Nota ini sudah dibatalkan sebelumnya.', true);
            return;
        }

        $invoice = htmlspecialchars($transaction->invoice_number);
        $nominal = number_format((float) $transaction->total, 0, ',', '.');
        $time = Carbon::parse($transaction->created_at)->translatedFormat('d M Y, H:i') . ' WIB';
        $cashier = htmlspecialchars($transaction->user?->name ?? 'Kasir');
        $orderType = ucwords(str_replace('_', ' ', $transaction->order_type ?? 'dine in'));

        $text = "⚠️ <b>KONFIRMASI PEMBATALAN NOTA (VOID)</b>\n";
        $text .= "────────────────────\n";
        $text .= "📄 Nota    : <code>#{$invoice}</code>\n";
        $text .= "🕒 Waktu   : {$time}\n";
        $text .= "👤 Kasir   : {$cashier}\n";
        $text .= "📦 Layanan : {$orderType}\n";
        $text .= "💰 Total   : <b>Rp {$nominal}</b>\n";
        $text .= "────────────────────\n";
        $text .= "Pilih alasan pembatalan di bawah ini:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📝 Salah Input Kasir', 'callback_data' => "void_do_{$txId}_salah_input"],
                    ['text' => '👤 Pelanggan Batal', 'callback_data' => "void_do_{$txId}_pelanggan_batal"],
                ],
                [
                    ['text' => '🔄 Double Transaksi', 'callback_data' => "void_do_{$txId}_double_trx"],
                    ['text' => '❓ Alasan Lainnya', 'callback_data' => "void_do_{$txId}_lainnya"],
                ],
                [
                    ['text' => '🔙 Batalkan / Jangan Hapus', 'callback_data' => "void_cancel_{$txId}"],
                    ['text' => '📋 Daftar Nota Lain', 'callback_data' => "menu_void_list"],
                ],
            ],
        ];

        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
        $this->telegramService->answerCallbackQuery($callbackId, 'Pilih alasan pembatalan nota');
    }

    /**
     * Batalkan dialog void dan kembalikan tampilan nota seperti semula.
     */
    public function handleVoidCancel(string $chatId, int $txId, ?int $messageId, string $callbackId): void
    {
        $transaction = Transaction::with(['details.product.category', 'user', 'shift', 'cancelledBy'])->find($txId);

        if (!$transaction) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Transaksi tidak ditemukan.', true);
            return;
        }

        $setting = $this->telegramService->getSetting();

        if ($transaction->status === 'cancelled') {
            $voidAlertText = $this->telegramService->formatVoidMessage($transaction, $setting);
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '📋 Log Void', 'callback_data' => 'cmd_void'],
                        ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                    ],
                ],
            ];
            $this->deliverResponse($chatId, $voidAlertText, $keyboard, $messageId);
            $this->telegramService->answerCallbackQuery($callbackId, 'Nota sudah dibatalkan sebelumnya.');
            return;
        }

        $text = $this->telegramService->formatTransactionMessage($transaction, $setting);
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🚫 Batalkan Nota (Void)', 'callback_data' => 'void_req_' . $transaction->id],
                ],
            ],
        ];

        $this->deliverResponse($chatId, $text, $keyboard, $messageId);
        $this->telegramService->answerCallbackQuery($callbackId, 'Pembatalan nota dibatalkan.');
    }

    /**
     * Eksekusi pembatalan nota (Void) di database secara atomic dan recalculate shift.
     */
    public function handleVoidExecution(string $chatId, int $txId, string $reasonKey, ?int $messageId, string $callbackId, string $fromName): void
    {
        $reasonMap = [
            'salah_input' => 'Salah Input Menu/Item oleh Kasir',
            'pelanggan_batal' => 'Pelanggan Membatalkan Pesanan',
            'double_trx' => 'Double Transaksi / Salah Cetak',
            'lainnya' => 'Dibatalkan oleh Owner/Admin via Telegram',
        ];
        $reasonText = $reasonMap[$reasonKey] ?? 'Dibatalkan oleh Owner/Admin via Telegram';

        DB::beginTransaction();
        try {
            $transaction = Transaction::with(['shift', 'user', 'details.product.category'])->lockForUpdate()->find($txId);

            if (!$transaction) {
                DB::rollBack();
                $this->telegramService->answerCallbackQuery($callbackId, 'Transaksi tidak ditemukan di database.', true);
                return;
            }

            if ($transaction->status === 'cancelled') {
                DB::rollBack();
                $this->telegramService->answerCallbackQuery($callbackId, 'Transaksi ini sudah dibatalkan sebelumnya.', true);
                $transaction->load(['cancelledBy', 'user', 'details.product.category', 'shift']);
                $setting = $this->telegramService->getSetting();
                $voidAlertText = $this->telegramService->formatVoidMessage($transaction, $setting);
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '📋 Log Void', 'callback_data' => 'cmd_void'],
                            ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                        ],
                    ],
                ];
                $this->deliverResponse($chatId, $voidAlertText, $keyboard, $messageId);
                return;
            }

            $adminUser = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();

            $transaction->status = 'cancelled';
            $transaction->cancelled_at = now();
            $transaction->cancelled_by = $adminUser ? $adminUser->id : null;
            $transaction->cancelled_reason = $reasonText . ' (via Telegram oleh ' . ($fromName ?: 'Owner') . ')';
            $transaction->save();

            // Hitung ulang omset dan laci shift kasir secara atomic
            if ($transaction->shift) {
                $transaction->shift->recalculateTotals();
            }

            DB::commit();

            // Tampilkan popup notifikasi sukses di layar HP owner
            $this->telegramService->answerCallbackQuery($callbackId, "✅ Nota #{$transaction->invoice_number} BERHASIL DIBATALKAN!", true);

            // Perbarui pesan Telegram menjadi format alert Void resmi dengan tombol navigasi
            $transaction->load(['cancelledBy', 'user', 'details.product.category', 'shift']);
            $setting = $this->telegramService->getSetting();
            $voidAlertText = $this->telegramService->formatVoidMessage($transaction, $setting);

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '📋 Log Void', 'callback_data' => 'cmd_void'],
                        ['text' => '🏠 Menu Utama', 'callback_data' => 'menu_main'],
                    ],
                ],
            ];

            $this->deliverResponse($chatId, $voidAlertText, $keyboard, $messageId);

            // Jika dieksekusi di chat pribadi dan ada group notifikasi toko terpisah, kirim alert ke group toko
            $settingChatId = $this->telegramService->getChatId($setting);
            if ($settingChatId && (string) $settingChatId !== (string) $chatId) {
                $this->telegramService->sendVoidNotification($transaction);
            }

            Log::info("Transaction {$transaction->invoice_number} successfully voided via Telegram by {$fromName}");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Telegram void execution failed: ' . $e->getMessage());
            $this->telegramService->answerCallbackQuery($callbackId, 'Gagal memproses void: ' . $e->getMessage(), true);
        }
    }
}
