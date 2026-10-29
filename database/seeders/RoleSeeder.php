<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Get all businesses
        $businesses = Business::all();

        foreach ($businesses as $business) {
            // Manager role
            $managerRole = Role::create([
                'business_id' => $business->id,
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Business manager with limited admin access',
                'is_system' => true,
            ]);

            // Assign manager permissions
            $managerPermissions = Permission::whereIn('key', [
                'business.view',
                'business.update',
                'user.view',
                'user.create',
                'module.view',
                'module.enable',
                'module.disable',
                'setting.view',
                'setting.update',
            ])->pluck('id');

            $managerRole->permissions()->sync($managerPermissions);

            // Employee role
            $employeeRole = Role::create([
                'business_id' => $business->id,
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Standard business employee',
                'is_system' => true,
            ]);

            // Assign employee permissions
            $employeePermissions = Permission::whereIn('key', [
                'user.view',
                'module.view',
                'setting.view',
            ])->pluck('id');

            $employeeRole->permissions()->sync($employeePermissions);
        }
    }
}