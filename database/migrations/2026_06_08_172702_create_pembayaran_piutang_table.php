<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_piutang', function (Blueprint $table) {
            $table->id('id_pembayaran');

            $table->unsignedBigInteger('id_piutang');

            $table->date('tanggal_pembayaran');
            $table->decimal('nominal_pembayaran', 15, 2)->default(0);

            $table->enum('metode_pembayaran', [
                'tunai',
                'transfer',
                'giro',
                'lainnya'
            ])->default('tunai');

            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('dibuat_oleh')->nullable();

            $table->timestamps();

            $table->foreign('id_piutang')
                ->references('id_piutang')
                ->on('piutang')
                ->onDelete('cascade')
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
        Schema::dropIfExists('pembayaran_piutang');
    }
};
