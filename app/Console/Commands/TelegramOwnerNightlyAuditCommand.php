<?php

namespace App\Console\Commands;

use App\Models\CashierShift;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\TelegramOwnerBotService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TelegramOwnerNightlyAuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:owner-nightly-audit
                            {--date= : Tanggal audit spesifik (format: YYYY-MM-DD, default: kemarin)}
                            {--force : Paksa kirim rekap finansial meskipun semua shift sudah ditutup normal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit keamanan kasir akhir hari (01:00 AM): Deteksi shift yang lupa ditutup, auto-reconcile, dan kirim laporan safety net';

    /**
     * Execute the console command.
     */
    public function handle(TelegramOwnerBotService $ownerBotService, TelegramService $telegramService): int
    {
        $botToken = $ownerBotService->getBotToken();
        $chatId = $ownerBotService->getChatId();

        if (empty($botToken) || empty($chatId)) {
            $this->warn('⚠️ Bot token atau Chat ID Owner belum diatur. Melewati eksekusi audit malam.');
            return Command::SUCCESS;
        }

        // Smart Time Detection:
        // Jika dijalankan dini hari (00:00 - 05:59) -> Target audit adalah hari kemarin (yesterday)
        // Jika dijalankan malam hari (06:00 - 23:59) -> Target audit adalah hari ini (today)
        $auditDate = $this->option('date')
            ?: (Carbon::now()->hour < 6
                ? Carbon::yesterday()->format('Y-m-d')
                : Carbon::today()->format('Y-m-d'));

        $dateDisplay = Carbon::parse($auditDate)->translatedFormat('l, d F Y');
        $force = (bool) $this->option('force');

        $isToday = ($auditDate === Carbon::today()->format('Y-m-d'));
        $dayWord = $isToday ? 'hari ini' : 'kemarin';
        $dayWordCapital = $isToday ? 'Hari Ini' : 'Kemarin';
        $currentTimeStr = Carbon::now()->format('H:i') . ' WIB';

        $this->info("🔍 Menjalankan Nightly Safety Net Audit untuk tanggal: {$auditDate} ({$dateDisplay})...");

        $setting = $telegramService->getSetting();
        $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));

        // 1. Cek shift yang masih 'open' pada tanggal tersebut
        $openShifts = CashierShift::with(['user', 'transactions'])
            ->whereDate('start_time', $auditDate)
            ->where('status', 'open')
            ->get();

        // 2. Cek transaksi yang selesai pada tanggal tersebut
        $completedTransactions = Transaction::whereDate('created_at', $auditDate)
            ->where('status', 'completed')
            ->get();

        $completedShifts = CashierShift::whereDate('start_time', $auditDate)
            ->where('status', 'closed')
            ->get();

        // KASUS 1: Ditemukan shift yang belum ditutup oleh kasir (Kelalaian / Lupa Tutup Shift)
        if ($openShifts->isNotEmpty()) {
            $this->warn("⚠️ Ditemukan {$openShifts->count()} shift yang BELUM DITUTUP oleh kasir!");

            $cashierNames = [];
            foreach ($openShifts as $shift) {
                $shift->recalculateTotals();
                $shift->status = 'closed';
                $shift->end_time = $shift->end_time ?: Carbon::parse($auditDate)->endOfDay();
                
                // Jika kas fisik belum diinput kasir, tandai expected cash
                if (is_null($shift->actual_cash)) {
                    $shift->actual_cash = $shift->expected_cash;
                    $shift->difference = 0;
                }

                $existingNotes = trim($shift->notes ?? '');
                $shift->notes = ($existingNotes ? $existingNotes . " | " : "") . "[Auto-Closed oleh Nightly Safety Net {$currentTimeStr} - Kasir Lupa Tutup Shift]";
                $shift->save();

                $cashierNames[] = $shift->user?->name ?? 'Kasir #' . $shift->user_id;
            }

            $cashierList = implode(', ', array_unique($cashierNames));
            $txCount = $completedTransactions->count();
            $totalSales = number_format($completedTransactions->sum('total'), 0, ',', '.');

            // Susun Pesan Peringatan Safety Net ke Owner
            $alertText = "⚠️ <b>PERINGATAN AUDIT MALAM (SAFETY NET {$currentTimeStr})</b>\n";
            $alertText .= "🏪 <b>{$shopName}</b>\n";
            $alertText .= "🗓️ Tanggal Pembukuan: <b>{$dateDisplay}</b>\n";
            $alertText .= "────────────────────────────\n";
            $alertText .= "🚨 <b>DITEMUKAN SHIFT KASIR BELUM DITUTUP!</b>\n";
            $alertText .= "Kasir yang bertugas {$dayWord} lupa melakukan prosedur tutup shift pada sistem POS sebelum pulang.\n\n";
            $alertText .= "👤 <b>Kasir Bertugas :</b> {$cashierList}\n";
            $alertText .= "🧾 <b>Total Transaksi:</b> {$txCount} Nota (Rp {$totalSales})\n";
            $alertText .= "────────────────────────────\n";
            $alertText .= "🛠️ <b>Tindakan Otomatis Sistem POS:</b>\n";
            $alertText .= "1. Shift kasir telah ditutup & dikunci otomatis oleh sistem agar data aman.\n";
            $alertText .= "2. Pembukuan transaksi {$dayWord} telah difinalisasi agar tidak bercampur dengan shift berikutnya.\n";
            $alertText .= "3. Ringkasan performa dan file Excel laporan {$dayWord} telah diracik di bawah ini.\n";
            $alertText .= "────────────────────────────\n";
            $alertText .= "ℹ️ <i>Disarankan mengingatkan kasir bersangkutan untuk selalu melakukan closing kas laci setiap selesai bertugas.</i>";

            // Kirim Alert Safety Net
            $telegramService->sendMessage($alertText, $botToken, $chatId);

            // Kirim Financial Card
            $ownerBotService->sendFinancialCard(
                $chatId,
                $auditDate,
                $auditDate,
                "Rekap Final {$dayWordCapital} ({$dateDisplay})"
            );

            // Kirim File Excel
            $ownerBotService->sendExcelReport(
                $chatId,
                $auditDate,
                $auditDate,
                "Laporan Penjualan {$dayWordCapital} ({$dateDisplay})"
            );

            $this->info("✅ Alert kelalaian shift kasir, kartu finansial, dan Excel laporan berhasil dikirim ke Owner.");
            return Command::SUCCESS;
        }

        // KASUS 2: Semua shift sudah ditutup normal oleh kasir
        if ($completedShifts->isNotEmpty() && !$force) {
            $this->info("✅ Semua shift kasir tanggal {$auditDate} telah ditutup dengan benar oleh kasir.");
            $this->info("🌙 Mode senyap aktif: Notifikasi tidak dikirim agar tidak mengganggu istirahat Owner.");
            return Command::SUCCESS;
        }

        // KASUS 3: Ada transaksi kemarin tapi tidak ada shift kasir yang tercatat (misal pesanan online/admin tanpa shift)
        if ($completedTransactions->isNotEmpty()) {
            $this->info("ℹ️ Terdapat transaksi tanpa sesi shift kasir formal. Mengirim rekap harian...");

            $notice = "🌙 <b>REKAP AKHIR HARI SISTEM ({$dateDisplay})</b>\n";
            $notice .= "🏪 <b>{$shopName}</b>\n";
            $notice .= "────────────────────────────\n";
            $notice .= "Berikut adalah rekapitulasi penjualan {$dayWord} untuk arsip pembukuan Anda.";

            $telegramService->sendMessage($notice, $botToken, $chatId);

            $ownerBotService->sendFinancialCard(
                $chatId,
                $auditDate,
                $auditDate,
                "Rekap {$dayWordCapital} ({$dateDisplay})"
            );

            $ownerBotService->sendExcelReport(
                $chatId,
                $auditDate,
                $auditDate,
                "Laporan Penjualan {$dayWordCapital} ({$dateDisplay})"
            );

            $this->info("✅ Rekap harian sistem berhasil dikirim ke Owner.");
            return Command::SUCCESS;
        }

        // KASUS 4: Cafe libur / tidak ada transaksi sama sekali
        $this->info("ℹ️ Tidak ada aktivitas transaksi pada tanggal {$auditDate}. Tidak ada notifikasi yang perlu dikirim.");
        return Command::SUCCESS;
    }
}
