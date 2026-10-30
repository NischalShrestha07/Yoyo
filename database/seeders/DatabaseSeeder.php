<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test admin user
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Create a test business
        $business = Business::create([
            'name' => 'Test Business',
            'slug' => 'test-business',
            'type' => 'general',
            'phone' => '+1234567890',
            'email' => 'business@example.com',
            'address' => '123 Test Street',
            'tax_id' => 'TAX123456',
            'is_active' => true,
            'created_by' => $adminUser->id,
        ]);

        // Create admin membership
        $business->memberships()->create([
            'user_id' => $adminUser->id,
            'status' => 'active',
            'is_default' => true,
            'joined_at' => now(),
        ]);

        // Create core permissions
        $permissions = [
            // Business management
            'business.view' => ['group' => 'Business', 'description' => 'View business details'],
            'business.create' => ['group' => 'Business', 'description' => 'Create new business'],
            'business.update' => ['group' => 'Business', 'description' => 'Update business details'],
            'business.delete' => ['group' => 'Business', 'description' => 'Delete business'],
            
            // User management
            'user.view' => ['group' => 'User', 'description' => 'View users'],
            'user.create' => ['group' => 'User', 'description' => 'Create users'],
            'user.update' => ['group' => 'User', 'description' => 'Update users'],
            'user.delete' => ['group' => 'User', 'description' => 'Delete users'],
            
            // Module management
            'module.view' => ['group' => 'Module', 'description' => 'View modules'],
            'module.enable' => ['group' => 'Module', 'description' => 'Enable modules'],
            'module.disable' => ['group' => 'Module', 'description' => 'Disable modules'],
            
            // Settings management
            'setting.view' => ['group' => 'Setting', 'description' => 'View settings'],
            'setting.update' => ['group' => 'Setting', 'description' => 'Update settings'],
        ];

        foreach ($permissions as $key => $data) {
            Permission::create([
                'key' => $key,
                'group' => $data['group'],
                'description' => $data['description'],
            ]);
        }

        // Create system roles
        $adminRole = Role::create([
            'business_id' => $business->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Full access to all business features',
            'is_system' => true,
        ]);

        $userRole = Role::create([
            'business_id' => $business->id,
            'name' => 'User',
            'slug' => 'user',
            'description' => 'Standard business user',
            'is_system' => true,
        ]);

        // Assign all permissions to admin role
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Assign basic permissions to user role
        $userRole->permissions()->sync([
            Permission::where('key', 'user.view')->first()->id,
            Permission::where('key', 'module.view')->first()->id,
            Permission::where('key', 'setting.view')->first()->id,
        ]);

        // Assign admin role to admin user
        $business->memberships()->where('user_id', $adminUser->id)->first()
            ->roles()->attach($adminRole->id);
    }
}