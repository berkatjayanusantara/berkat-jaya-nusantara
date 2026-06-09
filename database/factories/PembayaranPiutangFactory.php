<?php

namespace Database\Factories;

use App\Models\PembayaranPiutang;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PembayaranPiutang>
 */
class PembayaranPiutangFactory extends Factory
{
    protected $model = PembayaranPiutang::class;

    public function definition(): array
    {
        return [
            'id_piutang' => Piutang::factory(),
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal_pembayaran' => fake()->numberBetween(10000, 100000),
            'metode_pembayaran' => fake()->randomElement(['tunai', 'transfer', 'giro', 'lainnya']),
            'catatan' => fake()->optional()->sentence(),
            'dibuat_oleh' => User::factory(),
        ];
    }
}
