<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\CashRegister;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class CashRegisterPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, CashRegister $cashRegister, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $cashRegister, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, CashRegister $cashRegister, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $cashRegister, $business);
    }

    public function delete(User $user, CashRegister $cashRegister, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $cashRegister, $business);
    }
}
