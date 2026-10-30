<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Business $business;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->actingAs($this->admin);

        $this->business = Business::factory()->create(['created_by' => $this->admin->id]);
        $this->business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $role = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);

        $this->business->memberships()->first()->roles()->attach($role->id);

        $this->category = Category::factory()->create(['business_id' => $this->business->id]);
    }

    public function test_custom_attributes_are_rejected_when_disabled(): void
    {
        $response = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'attributes' => ['colour' => 'Red'],
        ]));

        $response->assertSessionHasErrors('attributes');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_custom_attributes_are_validated_against_the_business_schema(): void
    {
        $this->business->setSetting('products.custom_attributes_enabled', true, 'bool');
        $this->business->setSetting('products.custom_attribute_schema', [
            ['key' => 'colour', 'label' => 'Colour', 'type' => 'select', 'options' => ['Red', 'Blue'], 'required' => true],
            ['key' => 'weight', 'label' => 'Weight', 'type' => 'number', 'required' => false],
        ], 'json');

        $invalid = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'sku' => 'SKU-ATTR-1',
            'attributes' => ['colour' => 'Green'],
        ]));
        $invalid->assertSessionHasErrors('attributes.colour');

        $valid = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'sku' => 'SKU-ATTR-2',
            'attributes' => ['colour' => 'Blue', 'weight' => '12.5'],
        ]));
        $valid->assertSessionHasNoErrors();

        $product = Product::where('sku', 'SKU-ATTR-2')->firstOrFail();
        $this->assertSame('Blue', $product->attributes['colour']);
    }

    public function test_variants_are_rejected_when_disabled(): void
    {
        $response = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'has_variants' => 1,
        ]));

        $response->assertSessionHasErrors('has_variants');
    }

    public function test_variants_can_be_created_when_enabled(): void
    {
        $this->business->setSetting('products.variants_enabled', true, 'bool');

        $this->post(route('business.products.store', $this->business), $this->productPayload([
            'sku' => 'SKU-VAR-1',
            'has_variants' => 1,
        ]))->assertSessionHasNoErrors();

        $product = Product::where('sku', 'SKU-VAR-1')->firstOrFail();
        $this->assertTrue($product->has_variants);

        $response = $this->post(route('business.products.variants.store', [$this->business, $product]), [
            'name' => 'Large',
            'sku' => 'VAR-LARGE',
            'barcode' => '1234567890123',
            'purchase_cost' => 5,
            'selling_price' => 9,
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'business_id' => $this->business->id,
            'sku' => 'VAR-LARGE',
            'name' => 'Large',
        ]);
    }

    public function test_variant_skus_are_unique_per_business(): void
    {
        $this->business->setSetting('products.variants_enabled', true, 'bool');

        $first = Product::factory()->create([
            'business_id' => $this->business->id,
            'has_variants' => true,
        ]);

        $second = Product::factory()->create([
            'business_id' => $this->business->id,
            'has_variants' => true,
        ]);

        $payload = [
            'name' => 'Variant',
            'sku' => 'SHARED-SKU',
            'purchase_cost' => 1,
            'selling_price' => 2,
        ];

        $this->post(route('business.products.variants.store', [$this->business, $first]), $payload)
            ->assertSessionHasNoErrors();

        $this->post(route('business.products.variants.store', [$this->business, $second]), $payload)
            ->assertSessionHasErrors('sku');
    }

    public function test_services_never_create_stock_movements(): void
    {
        $response = $this->post(route('business.services.store', $this->business), [
            'name' => 'Repair service',
            'slug' => 'repair-service',
            'code' => 'SVC-1',
            'category_id' => $this->category->id,
            'selling_price' => 25,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('services', [
            'business_id' => $this->business->id,
            'code' => 'SVC-1',
        ]);
        $this->assertSame(0, StockMovement::where('business_id', $this->business->id)->count());
    }

    public function test_cross_business_categories_cannot_be_assigned_to_products(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherCategory = Category::factory()->create(['business_id' => $otherBusiness->id]);

        $response = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'category_id' => $otherCategory->id,
        ]));

        $response->assertSessionHasErrors('category_id');
    }

    public function test_product_skus_are_unique_only_within_a_business(): void
    {
        $otherBusiness = Business::factory()->create();
        Product::factory()->create([
            'business_id' => $otherBusiness->id,
            'sku' => 'SAME-SKU',
        ]);

        $response = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'sku' => 'SAME-SKU',
        ]));

        $response->assertSessionHasNoErrors();

        $this->assertSame(2, Product::where('sku', 'SAME-SKU')->count());
    }

    public function test_product_images_can_be_uploaded(): void
    {
        Storage::fake('public');

        $response = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'sku' => 'SKU-IMG-1',
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]));

        $response->assertSessionHasNoErrors();

        $product = Product::where('sku', 'SKU-IMG-1')->firstOrFail();

        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images[0]);
    }

    public function test_units_of_measurement_are_business_scoped(): void
    {
        $payload = [
            'name' => 'Pieces',
            'code' => 'PCS',
            'is_active' => 1,
        ];

        $this->post(route('business.units.store', $this->business), $payload)
            ->assertSessionHasNoErrors();

        $this->post(route('business.units.store', $this->business), $payload)
            ->assertSessionHasErrors('code');

        $otherBusiness = Business::factory()->create();

        $this->post(route('business.units.store', $otherBusiness), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame(2, UnitOfMeasurement::where('code', 'PCS')->count());
    }

    public function test_cross_business_units_cannot_be_assigned_to_products(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherUnit = UnitOfMeasurement::factory()->create(['business_id' => $otherBusiness->id]);

        $response = $this->post(route('business.products.store', $this->business), $this->productPayload([
            'unit_of_measurement_id' => $otherUnit->id,
        ]));

        $response->assertSessionHasErrors('unit_of_measurement_id');
    }

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test product',
            'sku' => 'SKU-'.uniqid(),
            'category_id' => $this->category->id,
            'type' => 'physical',
            'purchase_cost' => 10,
            'selling_price' => 20,
            'track_inventory' => 1,
            'is_active' => 1,
        ], $overrides);
    }
}
