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
        // 1. Tabel Pelanggan / Members
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Pelanggan');
                $table->string('phone', 30)->unique()->index();
                $table->integer('points_balance')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Riwayat Mutasi Poin
        if (!Schema::hasTable('customer_point_histories')) {
            Schema::create('customer_point_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
                $table->string('type', 20); // earn, redeem, adjust, expire
                $table->integer('points'); // misal: +3 atau -10
                $table->integer('balance_after');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tambah Kolom Loyalty ke Tabel Transactions
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'points_earned')) {
                $table->integer('points_earned')->default(0)->after('customer_name');
            }
            if (!Schema::hasColumn('transactions', 'points_redeemed')) {
                $table->integer('points_redeemed')->default(0)->after('points_earned');
            }
            if (!Schema::hasColumn('transactions', 'points_discount_amount')) {
                $table->decimal('points_discount_amount', 12, 2)->default(0)->after('points_redeemed');
            }
            if (!Schema::hasColumn('transactions', 'claim_token')) {
                $table->string('claim_token', 64)->nullable()->unique()->after('order_token');
            }
            if (!Schema::hasColumn('transactions', 'points_claimed_at')) {
                $table->timestamp('points_claimed_at')->nullable()->after('claim_token');
            }
        });

        // 4. Tambah Pengaturan Poin ke Tabel Settings
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'point_enabled')) {
                $table->boolean('point_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'point_spend_per_point')) {
                $table->integer('point_spend_per_point')->default(10000); // Tiap kelipatan 10k dapat 1 poin
            }
            if (!Schema::hasColumn('settings', 'point_exchange_rate')) {
                $table->integer('point_exchange_rate')->default(1000); // 1 poin senilai Rp 1.000
            }
            if (!Schema::hasColumn('settings', 'point_min_redeem')) {
                $table->integer('point_min_redeem')->default(5); // Minimal 5 poin untuk ditukar
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'point_enabled',
                'point_spend_per_point',
                'point_exchange_rate',
                'point_min_redeem',
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'points_earned',
                'points_redeemed',
                'points_discount_amount',
                'claim_token',
                'points_claimed_at',
            ]);
        });

        Schema::dropIfExists('customer_point_histories');
        Schema::dropIfExists('customers');
    }
};
