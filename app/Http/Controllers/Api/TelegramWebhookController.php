<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotCommandService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramBotCommandService $commandService;
    protected TelegramService $telegramService;

    public function __construct(TelegramBotCommandService $commandService, TelegramService $telegramService)
    {
        $this->commandService = $commandService;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming webhook updates directly from Telegram Bot API.
     * POST /api/telegram/webhook
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $update = $request->all();

        if (empty($update)) {
            return response()->json(['ok' => false, 'message' => 'Empty payload'], 400);
        }

        // Jalankan pemrosesan command / callback query
        $this->commandService->handleWebhookUpdate($update);

        return response()->json(['ok' => true]);
    }

    /**
     * Cek status webhook bot saat ini.
     * GET /api/telegram/webhook-info
     */
    public function getWebhookInfo(): JsonResponse
    {
        $info = $this->telegramService->getWebhookInfo();

        return response()->json([
            'success' => $info['success'] ?? false,
            'data' => $info['data'] ?? null,
        ]);
    }
}
