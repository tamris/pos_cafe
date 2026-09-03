<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 6)->nullable()->after('password');
        });

        // Set default 6-digit PIN for existing users
        // Admin / Owner default: 123456
        // Kasir default: 112233
        DB::table('users')->where('role', 'admin')->update(['pin' => '123456']);
        DB::table('users')->whereIn('role', ['kasir', 'cashier'])->update(['pin' => '112233']);
        DB::table('users')->whereNull('pin')->update(['pin' => '123456']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
