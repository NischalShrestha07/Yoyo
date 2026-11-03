<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\JournalEntry;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class JournalEntryPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, JournalEntry $journalEntry, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $journalEntry, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, JournalEntry $journalEntry, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $journalEntry, $business);
    }

    public function delete(User $user, JournalEntry $journalEntry, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $journalEntry, $business);
    }
}
