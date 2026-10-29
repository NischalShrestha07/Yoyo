<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessSetting>
 */
class BusinessSettingFactory extends Factory
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
            'value' => fake()->text(),
            'type' => fake()->randomElement(['string', 'integer', 'float', 'boolean', 'array']),
            'is_encrypted' => false,
        ];
    }
}