<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;
use App\Models\Business;

class StockMovementPolicy
{
    public function viewAny(User $user, Business $business): bool
    {
        return $user->is_active && $business->users()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Business $business, StockMovement $stockMovement): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $stockMovement->business_id === $business->id;
    }

    public function create(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function adjust(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function transfer(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }
}
