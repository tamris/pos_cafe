<?php

namespace App\Console\Commands;

use App\Services\TelegramOwnerBotService;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramOwnerBotSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:owner-setup
                            {--url= : Override webhook URL (default: APP_URL/api/telegram/owner/webhook)}
                            {--info : Tampilkan status webhook Telegram Owner Bot saat ini}
                            {--remove : Hapus webhook dari Telegram Bot API}
                            {--test : Kirim pesan tes ke Chat ID Owner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup webhook dan daftarkan menu resmi Telegram Bot Khusus Owner (Laporan & Excel)';

    /**
     * Execute the console command.
     */
    public function handle(TelegramOwnerBotService $ownerBotService, TelegramService $telegramService): int
    {
        $botToken = $ownerBotService->getBotToken();
        $chatId = $ownerBotService->getChatId();

        if (empty($botToken)) {
            $this->error('❌ Token Telegram Owner Bot belum diatur di .env (TELEGRAM_OWNER_BOT_TOKEN) atau database.');
            return 1;
        }

        // 1. Opsi Cek Info Webhook
        if ($this->option('info')) {
            $this->info('🔍 Mengecek status webhook Telegram Owner Bot...');
            $res = $telegramService->getWebhookInfo($botToken);

            if (!($res['success'] ?? false)) {
                $this->error('Gagal mengambil info webhook: ' . ($res['message'] ?? 'Error'));
                return 1;
            }

            $data = $res['data'] ?? [];
            $this->table(
                ['Parameter', 'Nilai'],
                [
                    ['Bot Token', substr($botToken, 0, 10) . '...' . substr($botToken, -5)],
                    ['Target Owner Chat ID', $chatId ?: '(Belum diatur)'],
                    ['Webhook URL', $data['url'] ?: '(Belum diatur / Kosong)'],
                    ['Has Custom Certificate', ($data['has_custom_certificate'] ?? false) ? 'Ya' : 'Tidak'],
                    ['Pending Update Count', $data['pending_update_count'] ?? 0],
                    ['Last Error Date', !empty($data['last_error_date']) ? date('Y-m-d H:i:s', $data['last_error_date']) : '-'],
                    ['Last Error Message', $data['last_error_message'] ?? '-'],
                    ['Max Connections', $data['max_connections'] ?? 40],
                ]
            );

            return 0;
        }

        // 2. Opsi Hapus Webhook
        if ($this->option('remove')) {
            $this->warn('Menghapus webhook Telegram Owner Bot...');
            $res = $telegramService->setWebhook('', $botToken);
            if ($res['success']) {
                $this->info('✅ Webhook Owner Bot berhasil dihapus.');
                return 0;
            }

            $this->error('Gagal menghapus webhook: ' . ($res['message'] ?? 'Error'));
            return 1;
        }

        // 3. Opsi Tes Kirim Pesan
        if ($this->option('test')) {
            if (empty($chatId)) {
                $this->error('❌ TELEGRAM_OWNER_CHAT_ID belum diatur.');
                return 1;
            }

            $this->info("Mengirim pesan tes ke Chat ID: {$chatId}...");
            $ownerBotService->sendWelcomeDashboard($chatId, 'Owner', 'Pesan uji coba koneksi Telegram Owner Bot.');
            $this->info('✅ Pesan tes dan persistent keyboard berhasil dikirim.');
            return 0;
        }

        // 4. Setup Webhook & Commands
        $rawUrl = $this->option('url');
        if (!empty($rawUrl)) {
            $trimmedUrl = rtrim($rawUrl, '/');
            if (!str_contains($trimmedUrl, '/api/telegram')) {
                $webhookUrl = $trimmedUrl . '/api/telegram/owner/webhook';
            } else {
                $webhookUrl = $trimmedUrl;
            }
        } else {
            $appUrl = rtrim(config('app.url'), '/');
            $webhookUrl = $appUrl . '/api/telegram/owner/webhook';
        }

        $this->info("🚀 Memulai setup Bot Telegram Khusus Owner (Executive Assistant)...");
        $this->line("📍 Bot Token: " . substr($botToken, 0, 10) . '...' . substr($botToken, -5));
        $this->line("📍 Target Webhook URL: {$webhookUrl}");

        // A. Set Webhook
        $webhookRes = $telegramService->setWebhook($webhookUrl, $botToken);
        if (!($webhookRes['success'] ?? false)) {
            $this->error('❌ Gagal mendaftarkan webhook: ' . ($webhookRes['message'] ?? 'Error'));
            return 1;
        }
        $this->info('✅ Webhook URL berhasil didaftarkan ke Telegram API.');

        // B. Daftarkan Menu Commands Resmi
        $commands = [
            ['command' => 'start', 'description' => 'Buka dashboard eksekutif & keyboard menu'],
            ['command' => 'hariini', 'description' => 'Rekap omset & performa hari ini'],
            ['command' => 'kemarin', 'description' => 'Rekap performa kemarin'],
            ['command' => 'minggu', 'description' => 'Rekap performa 7 hari terakhir'],
            ['command' => 'bulan', 'description' => 'Rekap performa bulan berjalan'],
            ['command' => 'laris', 'description' => 'Ranking menu terlaris (best seller)'],
            ['command' => 'excel', 'description' => 'Pusat unduh laporan Excel (.xlsx)'],
            ['command' => 'laba', 'description' => 'Analisis laba kotor, HPP, & net profit'],
            ['command' => 'help', 'description' => 'Panduan perintah & bantuan'],
        ];

        $cmdRes = $telegramService->setMyCommands($commands, $botToken);
        if ($cmdRes['success'] ?? false) {
            $this->info('✅ Menu perintah resmi (/commands) berhasil didaftarkan ke Telegram.');
        } else {
            $this->warn('⚠️ Webhook aktif, tetapi gagal update commands: ' . ($cmdRes['message'] ?? ''));
        }

        $this->newLine();
        $this->info('🎉 Setup Telegram Owner Bot SELESAI!');
        $this->line('👉 Owner bisa langsung membuka chat bot dan menekan /start.');
        $this->line('👉 Tombol menu bawah keyboard akan otomatis muncul dan siap digunakan.');

        return 0;
    }
}
