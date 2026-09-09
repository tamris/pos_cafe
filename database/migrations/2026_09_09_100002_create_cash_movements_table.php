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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('category_name');
            $table->enum('type', ['in', 'out'])->comment('in: Kas Masuk / Pay In, out: Kas Keluar / Pay Out');
            $table->enum('source', ['drawer', 'bank', 'petty_cash'])->default('drawer')->comment('drawer: Laci Kasir, bank: Rekening Bank, petty_cash: Kas Toko/Brankas');
            $table->decimal('amount', 15, 2);
            $table->text('notes');
            $table->string('receipt_image')->nullable();
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for lightning fast queries & reports
            $table->index(['shift_id', 'source']);
            $table->index(['movement_date', 'type']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
