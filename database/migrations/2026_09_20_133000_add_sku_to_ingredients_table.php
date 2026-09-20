<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ingredients', 'sku')) {
            Schema::table('ingredients', function (Blueprint $table) {
                $table->string('sku', 100)->nullable()->after('category');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ingredients', 'sku')) {
            Schema::table('ingredients', function (Blueprint $table) {
                $table->dropColumn('sku');
            });
        }
    }
};