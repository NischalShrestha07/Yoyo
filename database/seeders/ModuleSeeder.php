<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Core modules
        $coreModules = [
            [
                'key' => 'inventory',
                'name' => 'Inventory Management',
                'group' => 'Core',
                'is_core' => true,
                'dependencies' => null,
                'description' => 'Manage inventory items, stock levels, and reordering',
            ],
            [
                'key' => 'sales',
                'name' => 'Sales Management',
                'group' => 'Core',
                'is_core' => true,
                'dependencies' => ['inventory'],
                'description' => 'Process sales, manage customers, and handle payments',
            ],
            [
                'key' => 'purchases',
                'name' => 'Purchase Management',
                'group' => 'Core',
                'is_core' => true,
                'dependencies' => ['inventory'],
                'description' => 'Manage vendor purchases and purchase orders',
            ],
            [
                'key' => 'customers',
                'name' => 'Customer Management',
                'group' => 'Core',
                'is_core' => true,
                'dependencies' => null,
                'description' => 'Manage customer profiles and relationships',
            ],
            [
                'key' => 'reports',
                'name' => 'Reporting & Analytics',
                'group' => 'Core',
                'is_core' => true,
                'dependencies' => ['sales', 'inventory'],
                'description' => 'Generate business reports and analytics',
            ],
        ];

        // Optional modules
        $optionalModules = [
            [
                'key' => 'pos',
                'name' => 'Point of Sale',
                'group' => 'Retail',
                'is_core' => false,
                'dependencies' => ['sales'],
                'description' => 'Retail point of sale system for in-store sales',
            ],
            [
                'key' => 'ecommerce',
                'name' => 'E-commerce',
                'group' => 'Online',
                'is_core' => false,
                'dependencies' => ['sales', 'customers'],
                'description' => 'Online store and website integration',
            ],
            [
                'key' => 'accounting',
                'name' => 'Accounting',
                'group' => 'Finance',
                'is_core' => false,
                'dependencies' => ['sales', 'purchases'],
                'description' => 'Financial accounting and bookkeeping',
            ],
            [
                'key' => 'hr',
                'name' => 'Human Resources',
                'group' => 'Management',
                'is_core' => false,
                'dependencies' => null,
                'description' => 'Employee management and HR functions',
            ],
            [
                'key' => 'appointments',
                'name' => 'Appointments',
                'group' => 'Service',
                'is_core' => false,
                'dependencies' => ['customers'],
                'description' => 'Appointment scheduling and management',
            ],
        ];

        // Insert core modules
        foreach ($coreModules as $module) {
            Module::create($module);
        }

        // Insert optional modules
        foreach ($optionalModules as $module) {
            Module::create($module);
        }
    }
}