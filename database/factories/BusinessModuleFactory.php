<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessModule>
 */
class BusinessModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enabled' => fake()->boolean(),
            'settings' => null,
            'enabled_at' => fake()->dateTime(),
            'disabled_at' => null,
        ];
    }
}