<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\PaymentMade;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessMembers;

class PaymentMadePolicy
{
    use AuthorizesBusinessMembers;

    public function viewAny(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function view(User $user, PaymentMade $paymentMade, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $paymentMade, $business);
    }

    public function create(User $user, ?Business $business = null): bool
    {
        return $business instanceof Business ? $this->isMember($user, $business) : $user->is_active;
    }

    public function update(User $user, PaymentMade $paymentMade, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $paymentMade, $business);
    }

    public function delete(User $user, PaymentMade $paymentMade, ?Business $business = null): bool
    {
        return $this->ownsModel($user, $paymentMade, $business);
    }
}
