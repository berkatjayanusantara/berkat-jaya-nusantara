<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('barang', 'jenis_ppn')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->string('jenis_ppn', 50)
                    ->default('ppn_dpp_nilai_lain')
                    ->after('kena_ppn');
            });
        }

        if (Schema::hasColumn('barang', 'kena_ppn') && Schema::hasColumn('barang', 'jenis_ppn')) {
            DB::table('barang')
                ->where('kena_ppn', false)
                ->update([
                    'jenis_ppn' => 'non_ppn',
                ]);

            DB::table('barang')
                ->where('kena_ppn', true)
                ->update([
                    'jenis_ppn' => 'ppn_dpp_nilai_lain',
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('barang', 'jenis_ppn')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->dropColumn('jenis_ppn');
            });
        }
    }
};
