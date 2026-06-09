<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->string('status_penerimaan')
                ->default('lengkap')
                ->after('affect_stock');
        });

        DB::table('pembelian')
            ->update([
                'status_penerimaan' => 'lengkap'
            ]);
    }

    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn('status_penerimaan');
        });
    }
};
