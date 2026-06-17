<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('barang', 'kena_ppn')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->boolean('kena_ppn')->default(true)->after('isi_per_satuan');
            });
        }

        if (!Schema::hasColumn('penjualan', 'subtotal_kena_ppn')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->decimal('subtotal_kena_ppn', 15, 2)->default(0)->after('subtotal');
                $table->decimal('subtotal_non_ppn', 15, 2)->default(0)->after('subtotal_kena_ppn');
            });
        }

        if (!Schema::hasColumn('detail_penjualan', 'kena_ppn')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->boolean('kena_ppn')->default(true)->after('isi_per_satuan');
                $table->decimal('dpp_ppn', 15, 2)->default(0)->after('kena_ppn');
                $table->decimal('nilai_ppn', 15, 2)->default(0)->after('dpp_ppn');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('detail_penjualan', 'kena_ppn')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->dropColumn(['kena_ppn', 'dpp_ppn', 'nilai_ppn']);
            });
        }

        if (Schema::hasColumn('penjualan', 'subtotal_kena_ppn')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->dropColumn(['subtotal_kena_ppn', 'subtotal_non_ppn']);
            });
        }

        if (Schema::hasColumn('barang', 'kena_ppn')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->dropColumn('kena_ppn');
            });
        }
    }
};
