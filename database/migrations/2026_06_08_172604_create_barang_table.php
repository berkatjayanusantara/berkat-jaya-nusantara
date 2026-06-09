<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id('id_barang');
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->string('satuan');
            $table->integer('stok_saat_ini')->default(0);
            $table->decimal('harga_beli_terakhir', 15, 2)->default(0);
            $table->decimal('harga_jual_default', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
