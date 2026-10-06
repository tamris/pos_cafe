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
            $table->string('telegram_owner_bot_token')->nullable()->after('telegram_notify_void');
            $table->string('telegram_owner_chat_id')->nullable()->after('telegram_owner_bot_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_owner_bot_token',
                'telegram_owner_chat_id',
            ]);
        });
    }
};
