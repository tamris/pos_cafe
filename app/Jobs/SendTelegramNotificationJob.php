<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika terjadi kendala jaringan.
     * Tidak termasuk release dari rate-limit (429).
     */
    public int $tries = 3;

    /**
     * Exponential backoff antar percobaan (detik).
     * Percobaan 1 → 10s, percobaan 2 → 30s, percobaan 3 → 60s.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Batas waktu eksekusi job (detik).
     */
    public int $timeout = 15;

    /**
     * Jumlah maksimal release akibat rate-limit (429) sebelum menyerah.
     */
    private const MAX_RATE_LIMIT_RELEASES = 3;

    public string $message;
    public ?string $token;
    public ?string $chatId;
    public int $rateLimitReleaseCount = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, ?string $token = null, ?string $chatId = null)
    {
        $this->message = $message;
        $this->token = $token;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        $result = $telegramService->sendMessage($this->message, $this->token, $this->chatId);

        if ($result['success'] ?? false) {
            return; // Berhasil terkirim
        }

        $statusCode = $result['status_code'] ?? 0;

        // === 429 Too Many Requests (Rate Limit) ===
        // Release job kembali ke queue dengan delay sesuai retry_after dari Telegram.
        // Tidak menghitung sebagai "attempts" karena bukan error permanen.
        if ($statusCode === 429) {
            $retryAfter = $result['retry_after'] ?? 60;
            $retryAfter = max(30, min($retryAfter, 600)); // Clamp 30s - 10m

            if ($this->rateLimitReleaseCount >= self::MAX_RATE_LIMIT_RELEASES) {
                Log::error('SendTelegramNotificationJob: Rate-limit 429 sudah ' . self::MAX_RATE_LIMIT_RELEASES . 'x, menyerah.', [
                    'chat_id' => $this->chatId,
                ]);
                $this->fail(new \RuntimeException('Telegram rate limit exceeded after ' . self::MAX_RATE_LIMIT_RELEASES . ' releases'));
                return;
            }

            $this->rateLimitReleaseCount++;

            Log::info("SendTelegramNotificationJob: Rate-limited (429), release ke-{$this->rateLimitReleaseCount} dengan delay {$retryAfter}s.", [
                'chat_id' => $this->chatId,
            ]);

            $this->release($retryAfter);
            return;
        }

        // === 403 Forbidden (Bot di-block / Chat ID invalid) ===
        // Error permanen, tidak perlu retry karena hasilnya pasti sama.
        if ($statusCode === 403) {
            Log::error('SendTelegramNotificationJob: Bot di-block oleh user atau Chat ID invalid (403). Tidak akan retry.', [
                'chat_id' => $this->chatId,
                'error' => $result['message'] ?? 'Forbidden',
            ]);
            $this->fail(new \RuntimeException('Telegram bot blocked (403): ' . ($result['message'] ?? 'Forbidden')));
            return;
        }

        // === 401 Unauthorized (Token invalid) ===
        // Error permanen karena token salah, tidak perlu retry.
        if ($statusCode === 401) {
            Log::error('SendTelegramNotificationJob: Bot token invalid (401). Cek konfigurasi TELEGRAM_BOT_TOKEN.', [
                'chat_id' => $this->chatId,
            ]);
            $this->fail(new \RuntimeException('Telegram bot token invalid (401)'));
            return;
        }

        // === Error lainnya (5xx, network, dll) ===
        // Throw exception agar Laravel queue retry otomatis dengan backoff.
        Log::warning('SendTelegramNotificationJob: Gagal mengirim pesan ke Telegram, akan retry.', [
            'chat_id' => $this->chatId,
            'status_code' => $statusCode,
            'error' => $result['message'] ?? 'Unknown error',
            'attempt' => $this->attempts(),
        ]);

        throw new \RuntimeException('Telegram send failed: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Handle a job failure setelah semua percobaan habis.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SendTelegramNotificationJob: Gagal permanen setelah semua percobaan.', [
            'error' => $exception?->getMessage(),
            'chat_id' => $this->chatId,
            'attempts' => $this->attempts(),
            'rate_limit_releases' => $this->rateLimitReleaseCount,
        ]);
    }
}
