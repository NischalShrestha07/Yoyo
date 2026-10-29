<?php

namespace Tests\Feature\Platform;

use App\Models\Business;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleTest extends TestCase
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

        // Create test modules
        $this->inventoryModule = Module::create([
            'key' => 'inventory',
            'name' => 'Inventory Management',
            'group' => 'Core',
            'is_core' => true,
        ]);

        $this->salesModule = Module::create([
            'key' => 'sales',
            'name' => 'Sales Management',
            'group' => 'Core',
            'is_core' => true,
        ]);

        $this->posModule = Module::create([
            'key' => 'pos',
            'name' => 'Point of Sale',
            'group' => 'Retail',
            'is_core' => false,
        ]);
    }

    public function test_module_edit_shows_all_modules(): void
    {
        $response = $this->get("/platform/businesses/{$this->business->id}/modules/edit");

        $response->assertStatus(200);
        $response->assertViewHas('modules', function ($modules) {
            return $modules->count() === 3;
        });
        $response->assertViewHas('businessModules');
    }

    public function test_can_enable_modules(): void
    {
        $moduleData = [
            'modules' => [
                $this->inventoryModule->id => [
                    'enabled' => true,
                    'settings' => [],
                ],
                $this->salesModule->id => [
                    'enabled' => true,
                    'settings' => [],
                ],
                $this->posModule->id => [
                    'enabled' => false,
                    'settings' => [],
                ],
            ],
        ];

        $response = $this->put("/platform/businesses/{$this->business->id}/modules", $moduleData);

        $response->assertRedirect("/platform/businesses/{$this->business->id}");
        
        // Check if modules were enabled/disabled
        $this->assertDatabaseHas('business_modules', [
            'business_id' => $this->business->id,
            'module_id' => $this->inventoryModule->id,
            'enabled' => true,
            'enabled_at' => now(),
        ]);

        $this->assertDatabaseHas('business_modules', [
            'business_id' => $this->business->id,
            'module_id' => $this->salesModule->id,
            'enabled' => true,
            'enabled_at' => now(),
        ]);

        $this->assertDatabaseHas('business_modules', [
            'business_id' => $this->business->id,
            'module_id' => $this->posModule->id,
            'enabled' => false,
            'disabled_at' => now(),
        ]);
    }

    public function test_can_update_module_settings(): void
    {
        // Enable module first
        $this->business->modules()->attach($this->inventoryModule->id, [
            'enabled' => true,
            'enabled_by' => $this->admin->id,
            'enabled_at' => now(),
        ]);

        $moduleData = [
            'modules' => [
                $this->inventoryModule->id => [
                    'enabled' => true,
                    'settings' => [
                        'low_stock_threshold' => 10,
                        'auto_reorder' => true,
                    ],
                ],
            ],
        ];

        $response = $this->put("/platform/businesses/{$this->business->id}/modules", $moduleData);

        $response->assertRedirect("/platform/businesses/{$this->business->id}");
        
        // Check if settings were updated
        $this->assertDatabaseHas('business_modules', [
            'business_id' => $this->business->id,
            'module_id' => $this->inventoryModule->id,
            'settings' => json_encode([
                'low_stock_threshold' => 10,
                'auto_reorder' => true,
            ]),
        ]);
    }
}