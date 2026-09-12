<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup
                            {--url= : Override webhook URL (default: APP_URL/api/telegram/webhook)}
                            {--info : Tampilkan status webhook Telegram saat ini}
                            {--remove : Hapus webhook dari Telegram Bot API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup webhook dan daftarkan menu perintah resmi Telegram Bot POS Cafe';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $botToken = $telegramService->getBotToken();

        if (empty($botToken)) {
            $this->error('❌ Telegram Bot Token belum diatur di .env atau database settings.');
            return 1;
        }

        // 1. Opsi Cek Info Webhook
        if ($this->option('info')) {
            $this->info('🔍 Mengecek status webhook Telegram...');
            $res = $telegramService->getWebhookInfo();

            if (!$res['success']) {
                $this->error('Gagal mengambil info webhook: ' . ($res['message'] ?? 'Error'));
                return 1;
            }

            $data = $res['data'] ?? [];
            $this->table(
                ['Parameter', 'Nilai'],
                [
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
            $this->warn('Menghapus webhook Telegram...');
            $res = $telegramService->setWebhook('');
            if ($res['success']) {
                $this->info('✅ Webhook berhasil dihapus.');
                return 0;
            }

            $this->error('Gagal menghapus webhook: ' . ($res['message'] ?? 'Error'));
            return 1;
        }

        // 3. Setup Webhook & Commands
        $appUrl = rtrim(config('app.url'), '/');
        $webhookUrl = $this->option('url') ?: ($appUrl . '/api/telegram/webhook');

        $this->info("🚀 Memulai setup Bot Telegram POS Cafe...");
        $this->line("📍 Target Webhook URL: {$webhookUrl}");

        // A. Set Webhook
        $webhookRes = $telegramService->setWebhook($webhookUrl);
        if (!$webhookRes['success']) {
            $this->error('❌ Gagal mendaftarkan webhook: ' . ($webhookRes['message'] ?? 'Error'));
            return 1;
        }
        $this->info('✅ Webhook URL berhasil didaftarkan ke Telegram API.');

        // B. Daftarkan Menu Commands ke BotFather / Telegram
        $commands = [
            ['command' => 'menu', 'description' => 'Tampilkan menu tombol interaktif'],
            ['command' => 'omset', 'description' => 'Laporan omset & uang masuk hari ini'],
            ['command' => 'laris', 'description' => '5 Menu paling laris hari ini'],
            ['command' => 'cup', 'description' => 'Total cup & kategori terjual'],
            ['command' => 'shift', 'description' => 'Status kasir aktif & saldo laci kasir'],
            ['command' => 'pengeluaran', 'description' => 'Catatan pengeluaran kas hari ini'],
            ['command' => 'kemarin', 'description' => 'Rekap & komparasi penjualan kemarin'],
            ['command' => 'void', 'description' => 'Log pesanan dibatalkan (void) hari ini'],
            ['command' => 'bulan', 'description' => 'Akumulasi omset bulan berjalan'],
            ['command' => 'bantuan', 'description' => 'Panduan lengkap penggunaan bot'],
        ];

        $cmdRes = $telegramService->setMyCommands($commands);
        if ($cmdRes['success']) {
            $this->info('✅ Daftar 10 perintah resmi bot berhasil didaftarkan ke Telegram.');
        } else {
            $this->warn('⚠️ Webhook aktif, tetapi gagal mendaftarkan daftar command: ' . ($cmdRes['message'] ?? 'Error'));
        }

        $this->newLine();
        $this->info('🎉 SETUP SELESAI!');
        $this->line('Sekarang Anda bisa membuka chat bot di Telegram dan mengetik:');
        $this->line('👉 /menu atau /omset untuk mencoba interaksi!');

        return 0;
    }
}
