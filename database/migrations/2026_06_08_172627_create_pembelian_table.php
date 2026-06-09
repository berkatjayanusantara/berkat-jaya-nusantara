<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id('id_pembelian');

            $table->string('nomor_pembelian')->unique();
            $table->date('tanggal_pembelian');

            $table->unsignedBigInteger('id_supplier');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('persentase_pajak', 5, 2)->default(0);
            $table->decimal('nilai_pajak', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);

            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('dibuat_oleh')->nullable();

            $table->timestamps();

            $table->foreign('id_supplier')
                ->references('id_supplier')
                ->on('suppliers')
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
        Schema::dropIfExists('pembelian');
    }
};
