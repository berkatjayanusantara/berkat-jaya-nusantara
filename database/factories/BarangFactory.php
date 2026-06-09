<?php

namespace Database\Factories;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barang>
 */
class BarangFactory extends Factory
{
    protected $model = Barang::class;

    public function definition(): array
    {
        return [
            'kode_barang' => 'BRG-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_barang' => fake()->words(3, true),
            'satuan' => fake()->randomElement(['pcs', 'dus', 'kg', 'sak', 'meter']),
            'stok_saat_ini' => fake()->numberBetween(0, 100),
            'harga_beli_terakhir' => fake()->numberBetween(1000, 100000),
            'harga_jual_default' => fake()->numberBetween(2000, 150000),
            'keterangan' => fake()->optional()->sentence(),
            'status_aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn(array $attributes) => [
            'status_aktif' => false,
        ]);
    }
}
