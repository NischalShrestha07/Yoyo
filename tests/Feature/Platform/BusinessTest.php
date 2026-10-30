<?php

namespace Tests\Feature\Platform;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and login as admin user
        $this->admin = User::factory()->create([
            'is_active' => true,
            'activated_at' => now(),
        ]);
        
        $this->actingAs($this->admin);
    }

    public function test_platform_dashboard_shows_businesses(): void
    {
        Business::factory()->count(3)->create();

        $response = $this->get('/platform/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('businesses', function ($businesses) {
            return $businesses->count() === 3;
        });
    }

    public function test_business_index_shows_businesses(): void
    {
        Business::factory()->count(3)->create();

        $response = $this->get('/platform/businesses');

        $response->assertStatus(200);
        $response->assertViewHas('businesses', function ($businesses) {
            return $businesses->count() === 3;
        });
    }

    public function test_business_creation_form(): void
    {
        $response = $this->get('/platform/businesses/create');

        $response->assertStatus(200);
        $response->assertViewIs('platform.businesses.create');
    }

    public function test_can_create_business(): void
    {
        $businessData = [
            'name' => 'Test Business',
            'type' => 'general',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'address' => '123 Test St',
            'tax_id' => 'TAX123456',
            'is_active' => true,
        ];

        $response = $this->post('/platform/businesses', $businessData);

        $response->assertRedirect('/platform/businesses');
        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Business',
            'email' => 'test@example.com',
        ]);

        // Check if creator is added as member
        $business = Business::where('name', 'Test Business')->first();
        $this->assertDatabaseHas('business_memberships', [
            'business_id' => $business->id,
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
        ]);
    }

    public function test_can_edit_business(): void
    {
        $business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        $business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $updatedData = [
            'name' => 'Updated Business Name',
            'type' => 'electronics',
            'email' => 'updated@example.com',
            'phone' => '+0987654321',
            'address' => '456 Updated St',
            'tax_id' => 'TAX654321',
            'is_active' => '0',
        ];

        $response = $this->put("/platform/businesses/{$business->id}", $updatedData);

        $response->assertRedirect('/platform/businesses');
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Updated Business Name',
            'type' => 'electronics',
        ]);
        
        // Check if is_active was updated
        $updatedBusiness = Business::find($business->id);
        $this->assertFalse($updatedBusiness->is_active);
    }

    public function test_can_delete_business(): void
    {
        $business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        $business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $response = $this->delete("/platform/businesses/{$business->id}");

        $response->assertRedirect('/platform/businesses');
        $this->assertSoftDeleted($business);
    }

    public function test_can_view_business_details(): void
    {
        $business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        $business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $response = $this->get("/platform/businesses/{$business->id}");

        $response->assertStatus(200);
        $response->assertViewHas('business', $business);
    }
}