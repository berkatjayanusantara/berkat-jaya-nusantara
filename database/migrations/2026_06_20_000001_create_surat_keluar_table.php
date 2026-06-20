<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id('id_surat');
            $table->string('nomor_surat')->unique();
            $table->date('tanggal_surat');
            $table->string('jenis_surat')->default('Surat Umum');
            $table->string('tujuan');
            $table->text('alamat_tujuan')->nullable();
            $table->string('perihal');
            $table->string('lampiran')->nullable();
            $table->text('pembuka')->nullable();
            $table->longText('isi_surat');
            $table->text('penutup')->nullable();
            $table->string('kota_ttd')->default('Jakarta');
            $table->string('nama_penandatangan');
            $table->string('jabatan_penandatangan')->nullable();
            $table->enum('status_surat', ['draft', 'final'])->default('final');
            $table->text('catatan_internal')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('dibuat_oleh')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
