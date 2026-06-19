<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('detail_penjualan', 'jenis_ppn')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->string('jenis_ppn', 50)
                    ->default('ppn_dpp_nilai_lain')
                    ->after('kena_ppn');
            });
        }

        if (!Schema::hasColumn('detail_penjualan', 'tarif_ppn')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->decimal('tarif_ppn', 5, 2)
                    ->default(0)
                    ->after('jenis_ppn');
            });
        }

        if (Schema::hasColumn('detail_penjualan', 'kena_ppn') && Schema::hasColumn('detail_penjualan', 'jenis_ppn')) {
            DB::table('detail_penjualan')
                ->where('kena_ppn', false)
                ->update([
                    'jenis_ppn' => 'non_ppn',
                    'tarif_ppn' => 0,
                ]);

            DB::table('detail_penjualan')
                ->where('kena_ppn', true)
                ->update([
                    'jenis_ppn' => 'ppn_dpp_nilai_lain',
                    'tarif_ppn' => 11,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('detail_penjualan', 'tarif_ppn')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->dropColumn('tarif_ppn');
            });
        }

        if (Schema::hasColumn('detail_penjualan', 'jenis_ppn')) {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->dropColumn('jenis_ppn');
            });
        }
    }
};
