<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramOwnerBotService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramOwnerWebhookController extends Controller
{
    protected TelegramOwnerBotService $ownerBotService;
    protected TelegramService $telegramService;

    public function __construct(TelegramOwnerBotService $ownerBotService, TelegramService $telegramService)
    {
        $this->ownerBotService = $ownerBotService;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming webhook updates from Telegram for Owner Bot.
     * POST /api/telegram/owner/webhook
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $update = $request->all();

        if (empty($update)) {
            return response()->json(['ok' => false, 'message' => 'Empty payload'], 400);
        }

        // Process message / callback query via TelegramOwnerBotService
        $this->ownerBotService->handleWebhookUpdate($update);

        return response()->json(['ok' => true]);
    }

    /**
     * Get webhook status for the owner bot.
     * GET /api/telegram/owner/webhook-info
     */
    public function getWebhookInfo(): JsonResponse
    {
        $token = $this->ownerBotService->getBotToken();
        $info = $this->telegramService->getWebhookInfo($token);

        return response()->json([
            'success' => $info['success'] ?? false,
            'data' => $info['data'] ?? null,
            'bot_configured' => !empty($token),
        ]);
    }
}
