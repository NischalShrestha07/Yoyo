<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        return [
            'business_id' => Business::factory(),
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'code' => strtoupper($this->faker->unique()->bothify('SRV-####')),
            'description' => $this->faker->sentence(),
            'selling_price' => $this->faker->randomFloat(2, 10, 200),
            'is_active' => true,
            'attributes' => [],
        ];
    }
}
