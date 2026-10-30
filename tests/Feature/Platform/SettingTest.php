<?php

namespace Tests\Feature\Platform;

use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
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

        // Create platform settings
        PlatformSetting::create([
            'key' => 'app_name',
            'value' => 'Retail ERP',
            'type' => 'string',
        ]);

        PlatformSetting::create([
            'key' => 'enable_registration',
            'value' => json_encode(true),
            'type' => 'boolean',
        ]);

        // Create a business
        $this->business = Business::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        // Add admin as member
        $membership = $this->business->memberships()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        // Assign admin role to admin user
        $adminRole = \App\Models\Role::create([
            'business_id' => $this->business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Full access to all business features',
            'is_system' => true,
        ]);

        $membership->roles()->attach($adminRole->id);
        
        

        // Create business settings
        BusinessSetting::create([
            'business_id' => $this->business->id,
            'key' => 'business_name',
            'value' => 'Test Business',
            'type' => 'string',
        ]);
    }

    public function test_platform_settings_index(): void
    {
        $response = $this->get('/platform/settings');

        $response->assertStatus(200);
        $response->assertViewHas('settings', function ($settings) {
            return $settings->count() === 2;
        });
    }

    public function test_platform_settings_edit(): void
    {
        $response = $this->get('/platform/settings/edit');

        $response->assertStatus(200);
        $response->assertViewHas('settings');
    }

    public function test_can_update_platform_settings(): void
    {
        $settingsData = [
            'settings' => [
                [
                    'key' => 'app_name',
                    'value' => 'Updated ERP System',
                ],
                [
                    'key' => 'enable_registration',
                    'value' => json_encode(false),
                ],
            ],
        ];

        $response = $this->put('/platform/settings', $settingsData);

        $response->assertRedirect('/platform/settings');
        
        // Check if settings were updated
        $this->assertDatabaseHas('platform_settings', [
            'key' => 'app_name',
            'value' => 'Updated ERP System',
        ]);

        $this->assertDatabaseHas('platform_settings', [
            'key' => 'enable_registration',
            'value' => json_encode(false),
        ]);
    }

    public function test_business_settings_edit(): void
    {
        $response = $this->get("/platform/businesses/{$this->business->id}/settings");

        $response->assertStatus(200);
        $response->assertViewHas('business', $this->business);
        $response->assertViewHas('settings');
    }

    public function test_can_update_business_settings(): void
    {
        $settingsData = [
            'settings' => [
                [
                    'key' => 'business_name',
                    'value' => 'Updated Business Name',
                ],
                [
                    'key' => 'currency',
                    'value' => 'EUR',
                ],
            ],
        ];

        $response = $this->put("/platform/businesses/{$this->business->id}/settings", $settingsData);

        $response->assertRedirect("/platform/businesses/{$this->business->id}");
        
        // Check if settings were updated
        $this->assertDatabaseHas('business_settings', [
            'business_id' => $this->business->id,
            'key' => 'business_name',
            'value' => 'Updated Business Name',
        ]);

        $this->assertDatabaseHas('business_settings', [
            'business_id' => $this->business->id,
            'key' => 'currency',
            'value' => 'EUR',
        ]);
    }
}