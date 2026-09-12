<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->change();
        });

        // Perbaiki data historis: transaksi yang statusnya pending dan belum dibayar (paid <= 0) ubah menjadi unpaid
        DB::table('transactions')
            ->where('status', 'pending')
            ->where('paid', '<=', 0)
            ->update(['payment_status' => 'unpaid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('paid')->change();
        });
    }
};
