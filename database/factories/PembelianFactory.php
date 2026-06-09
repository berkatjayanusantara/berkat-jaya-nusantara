<?php

namespace Database\Factories;

use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembelian>
 */
class PembelianFactory extends Factory
{
    protected $model = Pembelian::class;

    public function definition(): array
    {
        return [
            'nomor_pembelian' => 'PB-' . now()->format('Ymd') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'is_historical' => false,
            'affect_stock' => true,
            'status_penerimaan' => 'lengkap',
            'nomor_dokumen_asli' => null,
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => Supplier::factory(),
            'subtotal' => 100000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'catatan' => fake()->optional()->sentence(),
            'dibuat_oleh' => User::factory(),
        ];
    }
}
