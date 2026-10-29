<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users
        $users = User::all();

        // Create test businesses
        $businesses = Business::factory(5)->create();

        // Assign random users to businesses
        foreach ($businesses as $business) {
            // Randomly select 2-5 users for this business
            $selectedUsers = $users->random(rand(2, 5));
            
            foreach ($selectedUsers as $user) {
                $business->memberships()->create([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'is_default' => $user->id === $business->created_by,
                    'joined_at' => now(),
                ]);
            }
        }
    }
}