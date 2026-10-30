<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class CategoryPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, Category $category, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $category, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, Category $category, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $category, $business);
    }

    public function delete(User $user, Category $category, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $category, $business);
    }
}
