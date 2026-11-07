<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class ProjectPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, Project $project, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $project, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, Project $project, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $project, $business);
    }

    public function delete(User $user, Project $project, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $project, $business);
    }
}
