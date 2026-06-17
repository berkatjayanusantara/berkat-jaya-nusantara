<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan', 'total_sebelum_penyesuaian')) {
                $table->decimal('total_sebelum_penyesuaian', 15, 2)->default(0)->after('pajak_ditambahkan');
            }

            if (!Schema::hasColumn('penjualan', 'jenis_penyesuaian_total')) {
                $table->enum('jenis_penyesuaian_total', [
                    'tidak_ada',
                    'tambah',
                    'kurang',
                ])->default('tidak_ada')->after('total_sebelum_penyesuaian');
            }

            if (!Schema::hasColumn('penjualan', 'nominal_penyesuaian_total')) {
                $table->decimal('nominal_penyesuaian_total', 15, 2)->default(0)->after('jenis_penyesuaian_total');
            }

            if (!Schema::hasColumn('penjualan', 'keterangan_penyesuaian_total')) {
                $table->text('keterangan_penyesuaian_total')->nullable()->after('nominal_penyesuaian_total');
            }
        });

        if (Schema::hasColumn('penjualan', 'total_sebelum_penyesuaian')) {
            DB::statement("UPDATE penjualan SET total_sebelum_penyesuaian = COALESCE(total_akhir, 0) WHERE COALESCE(total_sebelum_penyesuaian, 0) = 0");
        }
    }

    public function down(): void
    {
        $columns = [
            'total_sebelum_penyesuaian',
            'jenis_penyesuaian_total',
            'nominal_penyesuaian_total',
            'keterangan_penyesuaian_total',
        ];

        $existingColumns = array_filter($columns, function ($column) {
            return Schema::hasColumn('penjualan', $column);
        });

        if (!empty($existingColumns)) {
            Schema::table('penjualan', function (Blueprint $table) use ($existingColumns) {
                $table->dropColumn($existingColumns);
            });
        }
    }
};
