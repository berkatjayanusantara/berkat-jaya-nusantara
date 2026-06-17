<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pembelian', 'biaya_lain')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->decimal('biaya_lain', 15, 2)->default(0)->after('pajak_ditambahkan');
            });
        }

        if (!Schema::hasColumn('pembelian', 'potongan_diskon')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->decimal('potongan_diskon', 15, 2)->default(0)->after('biaya_lain');
            });
        }

        if (!Schema::hasColumn('pembelian', 'keterangan_penyesuaian_total')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->text('keterangan_penyesuaian_total')->nullable()->after('potongan_diskon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pembelian', 'keterangan_penyesuaian_total')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->dropColumn('keterangan_penyesuaian_total');
            });
        }

        if (Schema::hasColumn('pembelian', 'potongan_diskon')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->dropColumn('potongan_diskon');
            });
        }

        if (Schema::hasColumn('pembelian', 'biaya_lain')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->dropColumn('biaya_lain');
            });
        }
    }
};
