<?php

namespace App\Policies;

use App\Models\StockLocation;
use App\Models\User;
use App\Models\Business;

class StockLocationPolicy
{
    public function viewAny(User $user, Business $business): bool
    {
        return $user->is_active && $business->users()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Business $business, StockLocation $stockLocation): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $stockLocation->business_id === $business->id;
    }

    public function create(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Business $business, StockLocation $stockLocation): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $stockLocation->business_id === $business->id;
    }

    public function delete(User $user, Business $business, StockLocation $stockLocation): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $stockLocation->business_id === $business->id;
    }
}
