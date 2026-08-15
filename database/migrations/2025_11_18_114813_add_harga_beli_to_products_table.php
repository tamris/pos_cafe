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
            $table->decimal('harga_beli', 15, 2)->default(0)->after('price');
            // 'decimal' -> Tipe data terbaik untuk uang (15 digit, 2 di belakang koma)
            // 'default(0)' -> Biar produk lama nilainya 0
            // 'after('price')' -> Biar rapi di database, letaknya setelah harga jual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('harga_beli');
        });
    }
};