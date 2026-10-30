<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class UnitOfMeasurementPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, UnitOfMeasurement $unit, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $unit, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, UnitOfMeasurement $unit, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $unit, $business);
    }

    public function delete(User $user, UnitOfMeasurement $unit, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $unit, $business);
    }
}
