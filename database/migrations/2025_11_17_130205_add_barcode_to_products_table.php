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
        Schema::table('products', function (Blueprint $table) {
            // Ini dia baris pentingnya
            $table->string('barcode')->unique()->nullable()->after('price');
            // 'unique()'   -> biar nggak ada barcode ganda (penting!)
            // 'nullable()' -> biar produk lama yg belum ada barcode-nya nggak error
            // 'after()'    -> opsional, biar rapi aja (misal setelah kolom 'price')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('barcode'); // Ini buat kalo mau di-rollback
        });
    }
};