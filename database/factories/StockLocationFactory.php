<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\StockLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockLocation>
 */
class StockLocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => $this->faker->randomElement(['Main Warehouse', 'Store Front', 'Backroom']),
            'code' => strtoupper($this->faker->unique()->bothify('LOC-###')),
            'address' => $this->faker->address(),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
