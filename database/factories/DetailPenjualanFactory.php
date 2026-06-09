<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailPenjualan>
 */
class DetailPenjualanFactory extends Factory
{
    protected $model = DetailPenjualan::class;

    public function definition(): array
    {
        $jumlah = fake()->numberBetween(1, 10);
        $hargaJual = fake()->numberBetween(1000, 100000);

        return [
            'id_penjualan' => Penjualan::factory(),
            'id_barang' => Barang::factory(),
            'jumlah' => $jumlah,
            'harga_jual' => $hargaJual,
            'subtotal' => $jumlah * $hargaJual,
        ];
    }
}
