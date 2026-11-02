<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\QuotationItem;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class QuotationItemPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, QuotationItem $quotationItem, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $quotationItem, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, QuotationItem $quotationItem, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $quotationItem, $business);
    }

    public function delete(User $user, QuotationItem $quotationItem, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $quotationItem, $business);
    }
}
