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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('stock', 12, 2)->default(0);
            $table->string('unit');
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('cost_per_unit', 12, 2)->default(0);
            $table->decimal('buy_price', 12, 2)->default(0);
            $table->decimal('buy_amount', 12, 2)->default(1);
            $table->string('buy_unit')->default('unit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
