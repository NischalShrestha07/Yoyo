<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'product_id' => Product::factory(),
            'business_id' => Business::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('VAR-####-???')),
            'barcode' => $this->faker->unique()->ean13(),
            'name' => ucfirst($name),
            'purchase_cost' => $this->faker->randomFloat(2, 1, 50),
            'selling_price' => $this->faker->randomFloat(2, 10, 100),
            'attributes' => ['size' => Str::upper($this->faker->randomLetter())],
            'is_active' => true,
        ];
    }
}
