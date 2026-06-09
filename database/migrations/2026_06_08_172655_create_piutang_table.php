<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piutang', function (Blueprint $table) {
            $table->id('id_piutang');

            $table->unsignedBigInteger('id_penjualan');
            $table->string('nomor_invoice');
            $table->unsignedBigInteger('id_customer');

            $table->decimal('total_piutang', 15, 2)->default(0);
            $table->decimal('total_dibayar', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2)->default(0);

            $table->date('tanggal_jatuh_tempo')->nullable();

            $table->enum('status_piutang', [
                'belum_lunas',
                'sebagian_dibayar',
                'lunas',
                'jatuh_tempo'
            ])->default('belum_lunas');

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->foreign('id_penjualan')
                ->references('id_penjualan')
                ->on('penjualan')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('id_customer')
                ->references('id_customer')
                ->on('customers')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutang');
    }
};
