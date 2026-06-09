<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\Piutang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Piutang>
 */
class PiutangFactory extends Factory
{
    protected $model = Piutang::class;

    public function definition(): array
    {
        $totalPiutang = fake()->numberBetween(100000, 1000000);
        $totalDibayar = fake()->numberBetween(0, $totalPiutang);
        $sisaPiutang = $totalPiutang - $totalDibayar;

        return [
            'id_penjualan' => Penjualan::factory()->kredit(),
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'id_customer' => Customer::factory(),
            'total_piutang' => $totalPiutang,
            'total_dibayar' => $totalDibayar,
            'sisa_piutang' => $sisaPiutang,
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
            'status_piutang' => $sisaPiutang <= 0 ? 'lunas' : ($totalDibayar > 0 ? 'sebagian_dibayar' : 'belum_lunas'),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
