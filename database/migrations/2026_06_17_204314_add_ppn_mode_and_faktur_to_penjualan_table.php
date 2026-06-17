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
            if (!Schema::hasColumn('penjualan', 'dpp_ppn')) {
                $table->decimal('dpp_ppn', 15, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('penjualan', 'mode_ppn')) {
                $table->enum('mode_ppn', [
                    'tanpa_ppn',
                    'include',
                    'exclude',
                ])->default('include')->after('persentase_pajak');
            }

            if (!Schema::hasColumn('penjualan', 'butuh_faktur_pajak')) {
                $table->boolean('butuh_faktur_pajak')->default(false)->after('pajak_ditambahkan');
            }

            if (!Schema::hasColumn('penjualan', 'nomor_faktur_pajak')) {
                $table->string('nomor_faktur_pajak', 100)->nullable()->after('butuh_faktur_pajak');
            }

            if (!Schema::hasColumn('penjualan', 'tanggal_faktur_pajak')) {
                $table->date('tanggal_faktur_pajak')->nullable()->after('nomor_faktur_pajak');
            }

            if (!Schema::hasColumn('penjualan', 'nama_faktur_pajak')) {
                $table->string('nama_faktur_pajak', 255)->nullable()->after('tanggal_faktur_pajak');
            }

            if (!Schema::hasColumn('penjualan', 'npwp_faktur_pajak')) {
                $table->string('npwp_faktur_pajak', 50)->nullable()->after('nama_faktur_pajak');
            }

            if (!Schema::hasColumn('penjualan', 'alamat_faktur_pajak')) {
                $table->text('alamat_faktur_pajak')->nullable()->after('npwp_faktur_pajak');
            }
        });

        // Mapping awal untuk data lama.
        // Data lama tidak diubah totalnya, hanya diberi mode PPN agar tetap kompatibel.
        if (Schema::hasColumn('penjualan', 'mode_ppn')) {
            DB::statement("
                UPDATE penjualan
                SET mode_ppn = CASE
                    WHEN COALESCE(persentase_pajak, 0) <= 0 THEN 'tanpa_ppn'
                    WHEN COALESCE(pajak_ditambahkan, 0) = 1 THEN 'exclude'
                    ELSE 'include'
                END
            ");
        }

        if (Schema::hasColumn('penjualan', 'dpp_ppn')) {
            DB::statement("
                UPDATE penjualan
                SET dpp_ppn = CASE
                    WHEN mode_ppn = 'include' THEN ROUND(COALESCE(subtotal, 0) * 100 / 111, 2)
                    ELSE COALESCE(subtotal, 0)
                END
            ");
        }
    }

    public function down(): void
    {
        $columns = [
            'dpp_ppn',
            'mode_ppn',
            'butuh_faktur_pajak',
            'nomor_faktur_pajak',
            'tanggal_faktur_pajak',
            'nama_faktur_pajak',
            'npwp_faktur_pajak',
            'alamat_faktur_pajak',
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
