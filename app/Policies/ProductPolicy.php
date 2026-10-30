<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\Business;

class ProductPolicy
{
    public function viewAny(User $user, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && $business->users()->where('user_id', $user->id)->exists();
        }
        return $user->is_active;
    }

    public function view(User $user, Product $product, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $product->business_id === $business->id;
        }
        if ($product->business_id) {
            $business = $product->business;
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

    public function update(User $user, Product $product, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $product->business_id === $business->id;
        }
        if ($product->business_id) {
            $business = $product->business;
            if ($business && $user->is_active && $business->users()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function archive(User $user, Product $product, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $product->business_id === $business->id;
        }
        if ($product->business_id) {
            $business = $product->business;
            if ($business && $user->is_active && $business->users()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function delete(User $user, Product $product, Business $business = null): bool
    {
        if ($business instanceof Business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $product->business_id === $business->id;
        }
        if ($product->business_id) {
            $business = $product->business;
            if ($business && $user->is_active && $business->users()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }
        return false;
    }
}
