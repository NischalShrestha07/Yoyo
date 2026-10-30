<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Models\Business;

class ServicePolicy
{
    public function viewAny(User $user, Business $business): bool
    {
        return $user->is_active && $business->users()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Business $business, Service $service): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $service->business_id === $business->id;
    }

    public function create(User $user, Business $business): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Business $business, Service $service): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $service->business_id === $business->id;
    }

    public function archive(User $user, Business $business, Service $service): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $service->business_id === $business->id;
    }

    public function delete(User $user, Business $business, Service $service): bool
    {
        return $user->is_active && 
               $business->users()->where('user_id', $user->id)->exists() &&
               $service->business_id === $business->id;
    }
}
