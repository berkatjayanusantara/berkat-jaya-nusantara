<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('id_customer');
            $table->string('kode_customer')->unique();
            $table->string('nama_customer');
            $table->string('nomor_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kategori_customer')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
