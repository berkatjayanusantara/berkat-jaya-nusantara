<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pembelian', function (Blueprint $table) {
            $table->integer('jumlah_dipesan')
                ->default(0)
                ->after('id_barang');
        });

        DB::table('detail_pembelian')
            ->update([
                'jumlah_dipesan' => DB::raw('jumlah')
            ]);
    }

    public function down(): void
    {
        Schema::table('detail_pembelian', function (Blueprint $table) {
            $table->dropColumn('jumlah_dipesan');
        });
    }
};
