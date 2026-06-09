<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'kode_supplier' => 'SUP-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_supplier' => fake()->company(),
            'nomor_telepon' => fake()->unique()->numerify('08##########'),
            'alamat' => fake()->address(),
            'catatan' => fake()->optional()->sentence(),
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
