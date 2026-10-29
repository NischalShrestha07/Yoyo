<?php

namespace Tests\Feature\Platform;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipTest extends TestCase
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

        // Create a business
        $this->business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        // Add admin as member
        $this->business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        // Create roles
        $this->adminRole = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);

        $this->userRole = Role::create([
            'business_id' => $this->business->id,
            'name' => 'User',
            'slug' => 'user',
            'is_system' => true,
        ]);
    }

    public function test_membership_index_shows_members(): void
    {
        // Create test members
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();

        $this->business->memberships()->create([
            'user_id' => $member1->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->business->memberships()->create([
            'user_id' => $member2->id,
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $response = $this->get("/platform/businesses/{$this->business->id}/memberships");

        $response->assertStatus(200);
        $response->assertViewHas('memberships', function ($memberships) {
            return $memberships->count() === 3; // 2 test members + admin
        });
    }

    public function test_can_invite_new_member(): void
    {
        $memberData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ];

        $response = $this->post("/platform/businesses/{$this->business->id}/memberships", $memberData);

        $response->assertRedirect("/platform/businesses/{$this->business->id}/memberships");
        
        // Check if user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Check if membership was created
        $user = User::where('email', 'john@example.com')->first();
        $this->assertDatabaseHas('business_memberships', [
            'business_id' => $this->business->id,
            'user_id' => $user->id,
            'status' => 'invited',
        ]);
    }

    public function test_can_update_member_status_and_roles(): void
    {
        // Create a test member
        $member = User::factory()->create();
        $membership = $this->business->memberships()->create([
            'user_id' => $member->id,
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $updateData = [
            'status' => 'active',
            'roles' => [$this->userRole->id],
        ];

        $response = $this->put("/platform/businesses/{$this->business->id}/memberships/{$membership->id}", $updateData);

        $response->assertRedirect("/platform/businesses/{$this->business->id}/memberships");
        
        // Check if status was updated
        $this->assertDatabaseHas('business_memberships', [
            'id' => $membership->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Check if role was assigned
        $this->assertDatabaseHas('membership_roles', [
            'business_membership_id' => $membership->id,
            'role_id' => $this->userRole->id,
        ]);
    }

    public function test_can_remove_member(): void
    {
        // Create a test member
        $member = User::factory()->create();
        $membership = $this->business->memberships()->create([
            'user_id' => $member->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->delete("/platform/businesses/{$this->business->id}/memberships/{$membership->id}");

        $response->assertRedirect("/platform/businesses/{$this->business->id}/memberships");
        
        // Check if membership was revoked
        $this->assertDatabaseHas('business_memberships', [
            'id' => $membership->id,
            'status' => 'inactive',
            'revoked_at' => now(),
        ]);
    }
}