<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TelegramNotificationApiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $cashierUser;
    protected ?Setting $setting = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashierUser = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->setting = Setting::first() ?? Setting::create([
            'shop_name' => 'Kopi Antigravity',
            'address' => 'Jl. Kopi No. 1',
        ]);

        $this->setting->update([
            'telegram_bot_token' => '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz',
            'telegram_chat_id' => '-100987654321',
            'telegram_notify_trx' => true,
            'telegram_notify_shift' => true,
            'telegram_notify_void' => true,
        ]);
    }

    public function test_unauthenticated_cannot_access_telegram_settings()
    {
        $response = $this->getJson('/api/admin/settings/telegram');
        $response->assertStatus(401);
    }

    public function test_cashier_cannot_access_admin_telegram_settings()
    {
        Sanctum::actingAs($this->cashierUser);

        $response = $this->getJson('/api/admin/settings/telegram');
        $response->assertStatus(403);
    }

    public function test_admin_can_get_telegram_settings_with_masked_token()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/settings/telegram');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_configured' => true,
                    'has_bot_token' => true,
                    'chat_id' => '-100987654321',
                    'notify_trx' => true,
                    'notify_shift' => true,
                    'notify_void' => true,
                ],
            ]);

        // Pastikan token bot dimasking untuk keamanan (tidak bocor utuh)
        $data = $response->json('data');
        $this->assertStringContainsString('...', $data['bot_token_masked']);
        $this->assertNotEquals('1234567890:ABCdefGHIjklMNOpqrsTUVwxyz', $data['bot_token_masked']);
    }

    public function test_admin_can_update_telegram_settings()
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'bot_token' => '9876543210:NEW_TOKEN_HERE_SAMPLE_123',
            'chat_id' => '1122334455',
            'notify_trx' => false,
            'notify_shift' => true,
            'notify_void' => true,
        ];

        $response = $this->postJson('/api/admin/settings/telegram', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_configured' => true,
                    'chat_id' => '1122334455',
                    'notify_trx' => false,
                    'notify_shift' => true,
                    'notify_void' => true,
                ],
            ]);

        $this->assertDatabaseHas('settings', [
            'telegram_bot_token' => '9876543210:NEW_TOKEN_HERE_SAMPLE_123',
            'telegram_chat_id' => '1122334455',
            'telegram_notify_trx' => false,
            'telegram_notify_shift' => true,
        ]);
    }

    public function test_admin_can_test_telegram_connection_success()
    {
        Sanctum::actingAs($this->adminUser);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 999,
                    'text' => 'Test message',
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/admin/settings/telegram/test', [
            'bot_token' => '123456:FAKE_TOKEN',
            'chat_id' => '12345678',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage') &&
                   $request['chat_id'] === '12345678';
        });
    }

    public function test_admin_test_telegram_connection_failure()
    {
        Sanctum::actingAs($this->adminUser);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok' => false,
                'error_code' => 401,
                'description' => 'Unauthorized: bot token is invalid',
            ], 401),
        ]);

        $response = $this->postJson('/api/admin/settings/telegram/test', [
            'bot_token' => 'invalid-token',
            'chat_id' => '12345678',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_pos_checkout_dispatches_telegram_notification_job()
    {
        Queue::fake();
        Sanctum::actingAs($this->cashierUser);

        $shift = CashierShift::create([
            'user_id' => $this->cashierUser->id,
            'shift_name' => 'Shift Pagi',
            'start_time' => now(),
            'starting_cash' => 100000,
            'status' => 'open',
        ]);

        $category = Category::create([
            'name' => 'Coffee Unique ' . uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'SKU-' . uniqid(),
            'name' => 'Espresso Hot Unique ' . uniqid(),
            'price' => 20000,
            'is_active' => true,
        ]);

        $payload = [
            'shift_id' => $shift->id,
            'payment_method' => 'cash',
            'order_type' => 'dine_in',
            'table_number' => '05',
            'customer_name' => 'Andi',
            'paid' => 50000,
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => 20000,
                    'quantity' => 2,
                    'notes' => 'Less sugar',
                ],
            ],
        ];

        $response = $this->postJson('/api/pos/checkout', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verifikasi bahwa Job notifikasi Telegram berhasil di-dispatch
        Queue::assertPushed(SendTelegramNotificationJob::class, function ($job) use ($product) {
            return str_contains($job->message, 'TRANSAKSI BARU') &&
                   str_contains($job->message, $product->name) &&
                   str_contains($job->message, 'Andi');
        });
    }

    public function test_pos_shift_end_dispatches_telegram_shift_notification_job()
    {
        Queue::fake();
        Sanctum::actingAs($this->cashierUser);

        $shift = CashierShift::create([
            'user_id' => $this->cashierUser->id,
            'shift_name' => 'Shift Pagi',
            'start_time' => now()->subHours(8),
            'starting_cash' => 200000,
            'status' => 'open',
        ]);

        $response = $this->postJson('/api/pos/shift/end', [
            'shift_id' => $shift->id,
            'actual_cash' => 500000,
            'notes' => 'Shift selesai dengan lancar',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Queue::assertPushed(SendTelegramNotificationJob::class, function ($job) {
            return str_contains($job->message, 'LAPORAN TUTUP SHIFT') &&
                   str_contains($job->message, 'Uang Fisik Dihitung') &&
                   str_contains($job->message, 'Shift selesai dengan lancar');
        });
    }

    public function test_admin_void_transaction_dispatches_void_notification_job()
    {
        Queue::fake();
        Sanctum::actingAs($this->adminUser);

        $transaction = Transaction::create([
            'invoice_number' => 'INV-20260907-' . rand(1000, 9999),
            'user_id' => $this->cashierUser->id,
            'subtotal' => 50000,
            'discount' => 0,
            'tax' => 0,
            'total' => 50000,
            'paid' => 50000,
            'change' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $response = $this->postJson("/api/admin/transactions/{$transaction->id}/void", [
            'reason' => 'Salah input item pesanan oleh kasir',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Queue::assertPushed(SendTelegramNotificationJob::class, function ($job) use ($transaction) {
            return str_contains($job->message, 'ALERT: TRANSAKSI DIBATALKAN / VOID') &&
                   str_contains($job->message, $transaction->invoice_number) &&
                   str_contains($job->message, 'Salah input item pesanan oleh kasir');
        });
    }

    public function test_telegram_service_handles_http_failure_without_crashing()
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response('Server Error', 500),
        ]);

        $service = app(TelegramService::class);
        $result = $service->sendMessage('<b>Test</b>', 'token', 'chat_id');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Telegram API Error', $result['message']);
    }

    public function test_telegram_service_reads_credentials_from_env_config_when_database_is_empty()
    {
        // Kosongkan pengaturan di database
        $this->setting->update([
            'telegram_bot_token' => null,
            'telegram_chat_id' => null,
        ]);

        // Simulasikan konfigurasi di config/services.php (.env)
        config([
            'services.telegram.bot_token' => 'ENV_TOKEN_12345:XYZ',
            'services.telegram.chat_id' => 'ENV_CHAT_9999',
            'services.telegram.notify_trx' => true,
        ]);

        $service = app(TelegramService::class);
        $this->assertTrue($service->isConfigured());
        $this->assertEquals('ENV_TOKEN_12345:XYZ', $service->getBotToken());
        $this->assertEquals('ENV_CHAT_9999', $service->getChatId());

        Sanctum::actingAs($this->adminUser);
        $response = $this->getJson('/api/admin/settings/telegram');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_configured' => true,
                    'chat_id' => 'ENV_CHAT_9999',
                    'source' => 'env',
                ],
            ]);
    }
}
