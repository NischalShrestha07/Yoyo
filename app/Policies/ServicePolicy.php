<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class ServicePolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, Service $service, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $service, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, Service $service, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $service, $business);
    }

    public function archive(User $user, Service $service, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $service, $business);
    }

    public function delete(User $user, Service $service, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $service, $business);
    }
}
