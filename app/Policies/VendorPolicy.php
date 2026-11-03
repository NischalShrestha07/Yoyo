<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class VendorPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, Vendor $vendor, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $vendor, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, Vendor $vendor, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $vendor, $business);
    }

    public function delete(User $user, Vendor $vendor, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $vendor, $business);
    }
}
