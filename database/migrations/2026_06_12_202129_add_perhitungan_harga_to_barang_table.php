<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->string('tipe_perhitungan_harga')
                ->default('normal')
                ->after('harga_jual_default');

            $table->string('satuan_hitung_harga')
                ->nullable()
                ->after('tipe_perhitungan_harga');

            $table->decimal('isi_per_satuan', 15, 3)
                ->default(1)
                ->after('satuan_hitung_harga');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_perhitungan_harga',
                'satuan_hitung_harga',
                'isi_per_satuan',
            ]);
        });
    }
};
