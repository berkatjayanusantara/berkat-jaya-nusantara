<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailPembelian>
 */
class DetailPembelianFactory extends Factory
{
    protected $model = DetailPembelian::class;

    public function definition(): array
    {
        $jumlahDipesan = fake()->numberBetween(1, 20);
        $jumlahDiterima = fake()->numberBetween(1, $jumlahDipesan);
        $hargaBeli = fake()->numberBetween(1000, 100000);

        return [
            'id_pembelian' => Pembelian::factory(),
            'id_barang' => Barang::factory(),
            'jumlah_dipesan' => $jumlahDipesan,
            'jumlah' => $jumlahDiterima,
            'harga_beli' => $hargaBeli,
            'subtotal' => $jumlahDiterima * $hargaBeli,
        ];
    }
}
