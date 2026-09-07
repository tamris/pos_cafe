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
     */
    public int $tries = 3;

    /**
     * Jeda antar percobaan (detik).
     */
    public int $backoff = 5;

    /**
     * Batas waktu eksekusi job (detik).
     */
    public int $timeout = 15;

    public string $message;
    public ?string $token;
    public ?string $chatId;

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

        if (!($result['success'] ?? false)) {
            Log::warning('SendTelegramNotificationJob: Gagal mengirim pesan ke Telegram', [
                'chat_id' => $this->chatId,
                'error' => $result['message'] ?? 'Unknown error',
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SendTelegramNotificationJob: Gagal setelah ' . $this->tries . ' percobaan.', [
            'error' => $exception?->getMessage(),
            'chat_id' => $this->chatId,
        ]);
    }
}
