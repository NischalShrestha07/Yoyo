<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Business $business, Module $module): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function enable(User $user, Business $business, Module $module): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $business->memberships()->where('user_id', $user->id)
                   ->where('status', 'active')
                   ->whereHas('roles', function ($query) {
                       $query->where('name', 'admin');
                   })->exists();
    }

    public function disable(User $user, Business $business, Module $module): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $business->memberships()->where('user_id', $user->id)
                   ->where('status', 'active')
                   ->whereHas('roles', function ($query) {
                       $query->where('name', 'admin');
                   })->exists();
    }

    public function update(User $user, Business $business, Module $module): bool
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