<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessMembership>
 */
class BusinessMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(['invited', 'active', 'suspended']),
            'is_default' => false,
            'invited_at' => fake()->dateTime(),
            'joined_at' => fake()->dateTime(),
        ];
    }
}