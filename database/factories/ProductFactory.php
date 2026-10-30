<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'business_id' => Business::factory(),
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => strtoupper($this->faker->unique()->bothify('PRD-####-???')),
            'barcode' => $this->faker->unique()->ean13(),
            'description' => $this->faker->sentence(),
            'type' => 'physical',
            'purchase_cost' => $this->faker->randomFloat(2, 1, 50),
            'selling_price' => $this->faker->randomFloat(2, 10, 100),
            'track_inventory' => true,
            'is_active' => true,
            'has_variants' => false,
            'allow_backorder' => false,
            'images' => [],
            'attributes' => [],
            'min_stock_level' => $this->faker->numberBetween(0, 10),
            'max_stock_level' => $this->faker->numberBetween(50, 100),
        ];
    }
}
