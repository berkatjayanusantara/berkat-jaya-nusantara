<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->string('tipe_perhitungan_harga')
                ->default('normal')
                ->after('harga_jual');

            $table->string('satuan_transaksi')
                ->nullable()
                ->after('tipe_perhitungan_harga');

            $table->string('satuan_hitung_harga')
                ->nullable()
                ->after('satuan_transaksi');

            $table->decimal('isi_per_satuan', 15, 3)
                ->default(1)
                ->after('satuan_hitung_harga');
        });
    }

    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_perhitungan_harga',
                'satuan_transaksi',
                'satuan_hitung_harga',
                'isi_per_satuan',
            ]);
        });
    }
};
