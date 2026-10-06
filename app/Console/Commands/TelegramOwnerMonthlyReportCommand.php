<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Transaction;
use App\Services\TelegramOwnerBotService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TelegramOwnerMonthlyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:owner-monthly-report
                            {--month= : Bulan target laporan dengan format YYYY-MM (default: bulan lalu)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim laporan eksekutif bulanan resmi dan spreadsheet Excel 4-sheet ke Bot Owner (Jadwal: Tanggal 1 Jam 08:00 AM)';

    /**
     * Execute the console command.
     */
    public function handle(TelegramOwnerBotService $ownerBotService, TelegramService $telegramService): int
    {
        $botToken = $ownerBotService->getBotToken();
        $chatId = $ownerBotService->getChatId();

        if (empty($botToken) || empty($chatId)) {
            $this->warn('⚠️ Bot token atau Chat ID Owner belum diatur. Melewati pengiriman laporan bulanan.');
            return Command::SUCCESS;
        }

        $monthInput = $this->option('month') ?: Carbon::now()->subMonth()->format('Y-m');
        $cDate = Carbon::parse($monthInput . '-01');
        $startDate = $cDate->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $cDate->copy()->endOfMonth()->format('Y-m-d');
        $monthTitle = $cDate->translatedFormat('F Y');

        $this->info("📊 Menyiapkan Laporan Eksekutif Bulanan untuk: {$monthTitle} ({$startDate} s/d {$endDate})...");

        $setting = $telegramService->getSetting();
        $shopName = htmlspecialchars(strtoupper($setting?->shop_name ?? 'POS CAFE'));

        // Cek apakah ada data transaksi pada bulan tersebut
        $hasTransactions = Transaction::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->exists();

        // 1. Pesan Pengantar Eksekutif (Morning Greeting)
        $introText = "🌅 <b>SELAMAT PAGI BOSS! • LAPORAN RESMI BULANAN</b>\n";
        $introText .= "🏪 <b>{$shopName}</b>\n";
        $introText .= "🗓️ Periode Pembukuan: <b>{$monthTitle}</b>\n";
        $introText .= "────────────────────────────\n";
        $introText .= "Bulan <b>{$monthTitle}</b> telah resmi berakhir dan ditutup!\n\n";
        $introText .= "Berikut kami sajikan rekapitulasi performa finansial, laba rugi, dan penjualan cafe Anda selama satu bulan penuh.\n\n";
        $introText .= "📎 <i>File spreadsheet Excel komprehensif 4-Sheet (Ringkasan Eksekutif, Laba Rugi & Arus Kas, Ranking Menu Terlaris, serta Buku Catatan Transaksi) telah dilampirkan di bawah ini.</i>";

        $telegramService->sendMessage($introText, $botToken, $chatId);

        // 2. Kirim Kartu Finansial Bulanan
        $ownerBotService->sendFinancialCard(
            $chatId,
            $startDate,
            $endDate,
            "Bulan Penuh {$monthTitle}"
        );

        // 3. Kirim File Excel 4-Sheet Lengkap
        $ownerBotService->sendExcelReport(
            $chatId,
            $startDate,
            $endDate,
            "Laporan Keuangan Bulanan {$monthTitle}"
        );

        $this->info("✅ Laporan Eksekutif Bulanan {$monthTitle} beserta spreadsheet Excel 4-sheet sukses dikirim ke Owner.");
        return Command::SUCCESS;
    }
}
