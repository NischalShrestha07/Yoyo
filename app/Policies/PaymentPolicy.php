<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class PaymentPolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function view(User $user, Payment $payment, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $payment, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business
            ? $this->isMember($user, $business)
            : $user->is_active;
    }

    public function update(User $user, Payment $payment, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $payment, $business);
    }

    public function delete(User $user, Payment $payment, ?Business $business = null): bool
    {
        return false;
    }
}
