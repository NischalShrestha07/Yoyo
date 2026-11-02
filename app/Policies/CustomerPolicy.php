<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class CustomerPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, Customer $customer, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $customer, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, Customer $customer, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $customer, $business);
    }

    public function delete(User $user, Customer $customer, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $customer, $business);
    }
}
