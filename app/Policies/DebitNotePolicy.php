<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\DebitNote;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class DebitNotePolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, DebitNote $debitNote, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $debitNote, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, DebitNote $debitNote, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $debitNote, $business);
    }

    public function delete(User $user, DebitNote $debitNote, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $debitNote, $business);
    }
}
