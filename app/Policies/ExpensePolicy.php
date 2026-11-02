<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Expense;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class ExpensePolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, Expense $expense, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $expense, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, Expense $expense, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $expense, $business);
    }

    public function delete(User $user, Expense $expense, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $expense, $business);
    }
}
