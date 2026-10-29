<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlatformSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, $setting): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, $setting): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, $setting): bool
    {
        return $user->is_active;
    }

    public function restore(User $user, $setting): bool
    {
        return $user->is_active;
    }

    public function forceDelete(User $user, $setting): bool
    {
        return $user->is_active;
    }

    public function updateAny(User $user): bool
    {
        return $user->is_active;
    }
}