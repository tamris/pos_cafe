<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('telegram_bot_token')->nullable()->after('receipt_footer');
            $table->string('telegram_chat_id')->nullable()->after('telegram_bot_token');
            $table->boolean('telegram_notify_trx')->default(false)->after('telegram_chat_id');
            $table->boolean('telegram_notify_shift')->default(false)->after('telegram_notify_trx');
            $table->boolean('telegram_notify_void')->default(false)->after('telegram_notify_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_bot_token',
                'telegram_chat_id',
                'telegram_notify_trx',
                'telegram_notify_shift',
                'telegram_notify_void',
            ]);
        });
    }
};
