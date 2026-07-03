<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('detail_penjualan', 'tanggal_pengantaran')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->date('tanggal_pengantaran')
                    ->nullable()
                    ->after('diskon_nominal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('detail_penjualan', 'tanggal_pengantaran')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->dropColumn('tanggal_pengantaran');
            });
        }
    }
};
