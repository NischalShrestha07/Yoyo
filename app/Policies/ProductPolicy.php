<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class ProductPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, Product $product, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $product, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, Product $product, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $product, $business);
    }

    public function archive(User $user, Product $product, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $product, $business);
    }

    public function delete(User $user, Product $product, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $product, $business);
    }
}
