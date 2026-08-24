<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Menerima Webhook HTTP Notification dari Midtrans
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received:', $payload);

        $result = $this->midtransService->handleNotification($payload);

        if (($result['status'] ?? '') === 'error') {
            return response()->json($result, 400);
        }

        return response()->json($result, 200);
    }
}
