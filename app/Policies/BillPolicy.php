<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\Business;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class BillPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, Bill $bill, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $bill, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, Bill $bill, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $bill, $business);
    }

    public function delete(User $user, Bill $bill, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $bill, $business);
    }
}
