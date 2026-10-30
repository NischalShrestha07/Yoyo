<?php

namespace App\Policies;

use App\Models\BusinessMembership;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MembershipPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Business $business, BusinessMembership $membership): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $business->memberships()->where('user_id', $user->id)
                   ->where('status', 'active')
                   ->whereHas('roles', function ($query) {
                       $query->where('name', 'admin');
                   })->exists();
    }

    public function update(User $user, Business $business, BusinessMembership $membership): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $business->memberships()->where('user_id', $user->id)
                   ->where('status', 'active')
                   ->whereHas('roles', function ($query) {
                       $query->where('name', 'admin');
                   })->exists();
    }

    public function delete(User $user, Business $business, BusinessMembership $membership): bool
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