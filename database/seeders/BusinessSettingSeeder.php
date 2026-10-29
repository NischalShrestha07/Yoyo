<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Get all businesses
        $businesses = Business::all();

        foreach ($businesses as $business) {
            // Common business settings
            $settings = [
                [
                    'key' => 'business_name',
                    'value' => $business->name,
                    'type' => 'string',
                    'is_encrypted' => false,
                ],
                [
                    'key' => 'currency',
                    'value' => 'USD',
                    'type' => 'string',
                    'is_encrypted' => false,
                ],
                [
                    'key' => 'timezone',
                    'value' => 'UTC',
                    'type' => 'string',
                    'is_encrypted' => false,
                ],
                [
                    'key' => 'tax_rate',
                    'value' => '0.08',
                    'type' => 'float',
                    'is_encrypted' => false,
                ],
                [
                    'key' => 'allow_negative_stock',
                    'value' => json_encode(false),
                    'type' => 'boolean',
                    'is_encrypted' => false,
                ],
                [
                    'key' => 'require_customer_for_sales',
                    'value' => json_encode(true),
                    'type' => 'boolean',
                    'is_encrypted' => false,
                ],
            ];

            foreach ($settings as $setting) {
                BusinessSetting::create(array_merge($setting, [
                    'business_id' => $business->id,
                ]));
            }
        }
    }
}