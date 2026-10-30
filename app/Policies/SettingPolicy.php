<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user): bool
    {
        return $user->is_active;
    }

    public function businessView(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function businessUpdate(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $business->memberships()->where('user_id', $user->id)
                   ->where('status', 'active')
                   ->whereHas('roles', function ($query) {
                       $query->where('name', 'admin');
                   })->exists();
    }
}