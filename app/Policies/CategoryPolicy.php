<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Models\Business;

class CategoryPolicy
{
    public function viewAny(User $user, Business $business = null): bool
    {
        if ($business) {
            return $user->is_active && $business->users()->where('user_id', $user->id)->exists();
        }
        return $user->is_active;
    }

    public function view(User $user, Category $category, Business $business = null): bool
    {
        // If business passed as second param in our custom usage
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $category->business_id === $business->id;
        }
        // If called as view(user, category) with business maybe not passed
        // Check if category belongs to any business user is member of
        if ($category->business_id) {
            $business = $category->business;
            if ($business && $user->is_active && $business->users()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function create(User $user, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && $business->users()->where('user_id', $user->id)->exists();
        }
        return $user->is_active;
    }

    public function update(User $user, Category $category, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $category->business_id === $business->id;
        }
        if ($category->business_id) {
            $business = $category->business;
            if ($business && $user->is_active && $business->users()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function delete(User $user, Category $category, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $category->business_id === $business->id;
        }
        if ($category->business_id) {
            $business = $category->business;
            if ($business && $user->is_active && $business->users()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }
        return false;
    }
}
