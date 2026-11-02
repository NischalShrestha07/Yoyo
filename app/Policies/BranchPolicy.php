<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class BranchPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, Branch $branch, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $branch, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, Branch $branch, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $branch, $business);
    }

    public function delete(User $user, Branch $branch, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $branch, $business);
    }
}
