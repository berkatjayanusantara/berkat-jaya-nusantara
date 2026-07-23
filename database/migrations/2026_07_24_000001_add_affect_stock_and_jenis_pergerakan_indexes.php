<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->index('affect_stock', 'idx_pembelian_affect_stock');
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->index('affect_stock', 'idx_penjualan_affect_stock');
        });

        Schema::table('riwayat_stok', function (Blueprint $table) {
            $table->index('jenis_pergerakan', 'idx_riwayat_jenis_pergerakan');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropIndex('idx_pembelian_affect_stock');
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropIndex('idx_penjualan_affect_stock');
        });

        Schema::table('riwayat_stok', function (Blueprint $table) {
            $table->dropIndex('idx_riwayat_jenis_pergerakan');
        });
    }
};
