<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\CreditNote;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class CreditNotePolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, CreditNote $creditNote, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $creditNote, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, CreditNote $creditNote, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $creditNote, $business);
    }

    public function delete(User $user, CreditNote $creditNote, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $creditNote, $business);
    }
}
