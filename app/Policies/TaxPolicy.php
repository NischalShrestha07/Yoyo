<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Tax;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class TaxPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, Tax $tax, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $tax, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, Tax $tax, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $tax, $business);
    }

    public function delete(User $user, Tax $tax, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $tax, $business);
    }
}
