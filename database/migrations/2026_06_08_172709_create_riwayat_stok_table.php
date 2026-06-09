<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id('id_riwayat_stok');

            $table->unsignedBigInteger('id_barang');

            $table->date('tanggal');

            $table->enum('jenis_pergerakan', [
                'masuk',
                'keluar',
                'penyesuaian'
            ]);

            $table->integer('jumlah');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');

            $table->string('sumber_transaksi')->nullable();
            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('dibuat_oleh')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_barang')
                ->references('id_barang')
                ->on('barang')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('dibuat_oleh')
                ->references('id_user')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_stok');
    }
};
