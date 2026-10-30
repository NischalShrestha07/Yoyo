<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'is_active' => true,
            'activated_at' => now(),
        ]);
        
        $this->actingAs($this->admin);
        
        $this->business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        
        $this->business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);
        
        $adminRole = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);
        
        $this->business->memberships()->first()->roles()->attach($adminRole->id);
        
        $this->category = Category::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function test_business_isolation_cannot_view_products_from_other_business(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherProduct = Product::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        
        $response = $this->get(route('business.products.show', [$this->business, $otherProduct]));
        
        $response->assertStatus(403);
    }

    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'type' => 'physical',
            'purchase_cost' => 10.00,
            'selling_price' => 20.00,
            'track_inventory' => true,
            'is_active' => true,
            'has_variants' => false,
            'allow_backorder' => false,
        ];
        
        $response = $this->post(route('business.products.store', $this->business), $productData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'business_id' => $this->business->id,
        ]);
    }

    public function test_duplicate_product_codes_within_same_business_scope_are_rejected(): void
    {
        Product::factory()->create([
            'business_id' => $this->business->id,
            'sku' => 'TEST-001',
        ]);
        
        $productData = [
            'name' => 'Test Product 2',
            'slug' => 'test-product-2',
            'sku' => 'TEST-001',
            'type' => 'physical',
            'purchase_cost' => 10.00,
            'selling_price' => 20.00,
        ];
        
        $response = $this->post(route('business.products.store', $this->business), $productData);
        
        $response->assertSessionHasErrors('sku');
    }

    public function test_product_validation_fails_with_missing_required_fields(): void
    {
        $response = $this->post(route('business.products.store', $this->business), []);
        
        $response->assertSessionHasErrors(['name', 'sku', 'type', 'purchase_cost', 'selling_price']);
    }

    public function test_can_list_products_with_pagination(): void
    {
        Product::factory()->count(20)->create([
            'business_id' => $this->business->id,
        ]);
        
        $response = $this->get(route('business.products.index', $this->business));
        
        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    public function test_services_without_physical_stock(): void
    {
        $serviceData = [
            'name' => 'Test Service',
            'slug' => 'test-service',
            'code' => 'SRV-001',
            'selling_price' => 50.00,
            'is_active' => true,
        ];
        
        $response = $this->post(route('business.services.store', $this->business), $serviceData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('services', [
            'name' => 'Test Service',
            'code' => 'SRV-001',
            'business_id' => $this->business->id,
        ]);
    }

    public function test_products_without_inventory_tracking(): void
    {
        $productData = [
            'name' => 'Non-Track Product',
            'slug' => 'non-track-product',
            'sku' => 'NT-001',
            'type' => 'physical',
            'purchase_cost' => 10.00,
            'selling_price' => 20.00,
            'track_inventory' => false,
        ];
        
        $response = $this->post(route('business.products.store', $this->business), $productData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'sku' => 'NT-001',
            'track_inventory' => false,
        ]);
    }
}

