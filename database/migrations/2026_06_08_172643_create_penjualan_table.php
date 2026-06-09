<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id('id_penjualan');

            $table->string('nomor_invoice')->unique();
            $table->date('tanggal_penjualan');

            $table->unsignedBigInteger('id_customer');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('persentase_pajak', 5, 2)->default(0);
            $table->decimal('nilai_pajak', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);

            $table->enum('metode_pembayaran', ['tunai', 'kredit'])->default('tunai');
            $table->enum('status_pembayaran', ['lunas', 'belum_lunas', 'sebagian'])->default('lunas');

            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('dibuat_oleh')->nullable();

            $table->timestamps();

            $table->foreign('id_customer')
                ->references('id_customer')
                ->on('customers')
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
        Schema::dropIfExists('penjualan');
    }
};
