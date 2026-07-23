<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── pembelian ────────────────────────────────────────────────────────
        Schema::table('pembelian', function (Blueprint $table) {
            // Filter utama di listing, laporan, dan dashboard
            $table->index('tanggal_pembelian', 'idx_pembelian_tanggal');
            $table->index('is_historical',     'idx_pembelian_historis');
            $table->index('status_penerimaan', 'idx_pembelian_status_terima');
            // Sering di-WHERE bersamaan pada dashboard
            $table->index(['tanggal_pembelian', 'is_historical'], 'idx_pembelian_tanggal_hist');
        });

        // ─── penjualan ────────────────────────────────────────────────────────
        Schema::table('penjualan', function (Blueprint $table) {
            $table->index('tanggal_penjualan',  'idx_penjualan_tanggal');
            $table->index('status_pembayaran',  'idx_penjualan_status_bayar');
            $table->index('is_historical',      'idx_penjualan_historis');
            $table->index('metode_pembayaran',  'idx_penjualan_metode');
            // Composite: laporan penjualan sering filter tanggal + status
            $table->index(['tanggal_penjualan', 'status_pembayaran'], 'idx_penjualan_tanggal_status');
        });

        // ─── piutang ──────────────────────────────────────────────────────────
        Schema::table('piutang', function (Blueprint $table) {
            $table->index('status_piutang',      'idx_piutang_status');
            $table->index('tanggal_jatuh_tempo', 'idx_piutang_jatuh_tempo');
            // Dashboard alert jatuh tempo: filter status + tanggal
            $table->index(['status_piutang', 'tanggal_jatuh_tempo'], 'idx_piutang_status_tempo');
        });

        // ─── riwayat_stok ─────────────────────────────────────────────────────
        Schema::table('riwayat_stok', function (Blueprint $table) {
            // Filter barang tertentu (RiwayatStokController filter id_barang)
            $table->index('id_barang', 'idx_riwayat_barang');
            // Dashboard whereDate('tanggal', today)
            $table->index('tanggal',   'idx_riwayat_tanggal');
            // Composite: filter barang + tanggal sekaligus
            $table->index(['id_barang', 'tanggal'], 'idx_riwayat_barang_tanggal');
            // created_at dipakai orderBy di beberapa listing
            $table->index('created_at', 'idx_riwayat_created');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropIndex('idx_pembelian_tanggal');
            $table->dropIndex('idx_pembelian_historis');
            $table->dropIndex('idx_pembelian_status_terima');
            $table->dropIndex('idx_pembelian_tanggal_hist');
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropIndex('idx_penjualan_tanggal');
            $table->dropIndex('idx_penjualan_status_bayar');
            $table->dropIndex('idx_penjualan_historis');
            $table->dropIndex('idx_penjualan_metode');
            $table->dropIndex('idx_penjualan_tanggal_status');
        });

        Schema::table('piutang', function (Blueprint $table) {
            $table->dropIndex('idx_piutang_status');
            $table->dropIndex('idx_piutang_jatuh_tempo');
            $table->dropIndex('idx_piutang_status_tempo');
        });

        Schema::table('riwayat_stok', function (Blueprint $table) {
            $table->dropIndex('idx_riwayat_barang');
            $table->dropIndex('idx_riwayat_tanggal');
            $table->dropIndex('idx_riwayat_barang_tanggal');
            $table->dropIndex('idx_riwayat_created');
        });
    }
};
