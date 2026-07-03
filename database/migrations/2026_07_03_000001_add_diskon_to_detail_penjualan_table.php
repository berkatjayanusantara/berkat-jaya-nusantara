<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('detail_penjualan', 'diskon_nominal')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->decimal('diskon_nominal', 15, 2)
                    ->default(0)
                    ->after('harga_jual');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('detail_penjualan', 'diskon_nominal')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->dropColumn('diskon_nominal');
            });
        }
    }
};
