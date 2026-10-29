<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(),
            'name' => fake()->word(),
            'group' => fake()->word(),
            'is_core' => fake()->boolean(),
            'dependencies' => null,
            'description' => fake()->sentence(),
        ];
    }
}