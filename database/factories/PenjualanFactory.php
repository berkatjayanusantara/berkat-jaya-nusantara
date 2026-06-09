<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Penjualan>
 */
class PenjualanFactory extends Factory
{
    protected $model = Penjualan::class;

    public function definition(): array
    {
        return [
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'is_historical' => false,
            'affect_stock' => true,
            'nomor_dokumen_asli' => null,
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => Customer::factory(),
            'subtotal' => 100000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
            'tanggal_jatuh_tempo' => null,
            'catatan' => fake()->optional()->sentence(),
            'dibuat_oleh' => User::factory(),
        ];
    }

    public function kredit(): static
    {
        return $this->state(fn(array $attributes) => [
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);
    }
}
