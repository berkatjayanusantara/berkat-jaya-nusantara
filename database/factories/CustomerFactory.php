<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'kode_customer' => 'CUS-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_customer' => fake()->name(),
            'nomor_telepon' => fake()->boolean(70) ? fake()->unique()->numerify('08##########') : null,
            'npwp' => fake()->boolean(30) ? fake()->numerify('##.###.###.#-###.###') : null,
            'alamat' => fake()->boolean(80) ? fake()->address() : null,
            'kategori_customer' => fake()->boolean(80) ? fake()->randomElement(['Umum', 'Retail', 'Grosir', 'Langganan']) : null,
            'catatan' => fake()->boolean(30) ? fake()->sentence() : null,
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
