<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class ChartOfAccountPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, ChartOfAccount $chartOfAccount, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $chartOfAccount, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, ChartOfAccount $chartOfAccount, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $chartOfAccount, $business);
    }

    public function delete(User $user, ChartOfAccount $chartOfAccount, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $chartOfAccount, $business);
    }
}
