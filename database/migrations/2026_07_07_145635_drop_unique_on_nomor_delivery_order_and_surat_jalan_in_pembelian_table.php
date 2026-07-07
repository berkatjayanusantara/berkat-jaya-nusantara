<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus unique constraint dari nomor_delivery_order dan nomor_surat_jalan
     * agar boleh duplikat antar transaksi pembelian (sama seperti perilaku
     * nomor_invoice di penjualan yang boleh diisi sama).
     */
    public function up(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            // Hapus unique index nomor_delivery_order jika ada
            try {
                $table->dropUnique(['nomor_delivery_order']);
            } catch (\Exception $e) {
                // Index mungkin sudah tidak ada, lanjutkan
            }

            // Hapus unique index nomor_surat_jalan jika ada
            try {
                $table->dropUnique(['nomor_surat_jalan']);
            } catch (\Exception $e) {
                // Index mungkin sudah tidak ada, lanjutkan
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            // Kembalikan unique index jika di-rollback
            $table->unique('nomor_delivery_order');
            $table->unique('nomor_surat_jalan');
        });
    }
};
