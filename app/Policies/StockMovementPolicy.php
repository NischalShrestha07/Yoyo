<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\StockMovement;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class StockMovementPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, StockMovement $stockMovement, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $stockMovement, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function adjust(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function transfer(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }
}
