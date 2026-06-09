<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatStok>
 */
class RiwayatStokFactory extends Factory
{
    protected $model = RiwayatStok::class;

    public function definition(): array
    {
        $stokSebelum = fake()->numberBetween(0, 100);
        $jumlah = fake()->numberBetween(1, 20);
        $jenis = fake()->randomElement(['masuk', 'keluar', 'penyesuaian']);

        $stokSesudah = match ($jenis) {
            'masuk' => $stokSebelum + $jumlah,
            'keluar' => max($stokSebelum - $jumlah, 0),
            default => $stokSebelum,
        };

        return [
            'id_barang' => Barang::factory(),
            'tanggal' => now()->toDateString(),
            'jenis_pergerakan' => $jenis,
            'jumlah' => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'sumber_transaksi' => 'TEST-' . fake()->unique()->numberBetween(1, 9999),
            'keterangan' => fake()->sentence(),
            'dibuat_oleh' => User::factory(),
            'created_at' => now(),
        ];
    }
}
