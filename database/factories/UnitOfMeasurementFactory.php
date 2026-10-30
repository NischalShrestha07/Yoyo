<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\UnitOfMeasurement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitOfMeasurement>
 */
class UnitOfMeasurementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => $this->faker->randomElement(['Pieces', 'Kilograms', 'Litres', 'Boxes']),
            'code' => strtoupper($this->faker->unique()->bothify('UOM-###')),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
