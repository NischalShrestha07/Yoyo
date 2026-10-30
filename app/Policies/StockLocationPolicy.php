<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\StockLocation;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class StockLocationPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, StockLocation $stockLocation, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $stockLocation, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, StockLocation $stockLocation, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $stockLocation, $business);
    }

    public function delete(User $user, StockLocation $stockLocation, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $stockLocation, $business);
    }
}
