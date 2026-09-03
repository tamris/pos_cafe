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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('order_source', 20)->default('pos')->after('order_type'); // 'pos', 'self_order'
            $table->string('payment_status', 20)->default('paid')->after('status'); // 'unpaid', 'paid', 'failed'
            $table->string('order_token', 64)->nullable()->unique()->after('invoice_number');
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['order_source', 'payment_status', 'order_token', 'customer_phone']);
        });
    }
};
