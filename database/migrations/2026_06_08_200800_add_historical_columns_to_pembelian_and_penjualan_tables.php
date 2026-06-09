<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->boolean('is_historical')->default(false)->after('nomor_pembelian');
            $table->boolean('affect_stock')->default(true)->after('is_historical');
            $table->string('nomor_dokumen_asli')->nullable()->after('affect_stock');
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->boolean('is_historical')->default(false)->after('nomor_invoice');
            $table->boolean('affect_stock')->default(true)->after('is_historical');
            $table->string('nomor_dokumen_asli')->nullable()->after('affect_stock');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn([
                'is_historical',
                'affect_stock',
                'nomor_dokumen_asli',
            ]);
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn([
                'is_historical',
                'affect_stock',
                'nomor_dokumen_asli',
            ]);
        });
    }
};
