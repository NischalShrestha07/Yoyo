<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
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
    }

    public function test_can_create_category(): void
    {
        $categoryData = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic products',
            'is_active' => true,
            'sort_order' => 1,
        ];
        
        $response = $this->post(route('business.categories.store', $this->business), $categoryData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'business_id' => $this->business->id,
        ]);
    }

    public function test_can_create_subcategory(): void
    {
        $parent = Category::factory()->create([
            'business_id' => $this->business->id,
        ]);
        
        $categoryData = [
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'parent_id' => $parent->id,
            'is_active' => true,
        ];
        
        $response = $this->post(route('business.categories.store', $this->business), $categoryData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Mobile Phones',
            'parent_id' => $parent->id,
            'business_id' => $this->business->id,
        ]);
    }

    public function test_business_isolation_for_categories(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherCategory = Category::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        
        $response = $this->get(route('business.categories.show', [$this->business, $otherCategory]));
        
        $response->assertStatus(403);
    }
}

