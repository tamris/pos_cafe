<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('order_type', ['dine_in', 'take_away', 'delivery'])->default('dine_in')->after('payment_method');
            $table->string('table_number')->nullable()->after('order_type');
            $table->string('customer_name')->nullable()->after('table_number');
            $table->enum('status', ['completed', 'preparing', 'cancelled'])->default('completed')->after('customer_name');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'table_number', 'customer_name', 'status']);
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
