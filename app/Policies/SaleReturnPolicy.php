<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\SaleReturn;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class SaleReturnPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, SaleReturn $saleReturn, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $saleReturn, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function refund(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function delete(User $user, SaleReturn $saleReturn, ?Business $business = null): bool
    {
        return false;
    }
}
