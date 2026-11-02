<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\SaleItem;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class SaleItemPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, SaleItem $saleItem, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $saleItem, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, SaleItem $saleItem, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $saleItem, $business);
    }

    public function delete(User $user, SaleItem $saleItem, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $saleItem, $business);
    }
}
