<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'type' => fake()->randomElement(['general', 'grocery', 'electronics', 'repair', 'clothing', 'fastfood']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'tax_id' => fake()->bothify('TAX-########'),
            'logo_path' => null,
            'is_active' => true,
            'created_by' => null, // Will be set in seeder
        ];
    }
}