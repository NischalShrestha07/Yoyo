<?php

namespace App\Policies\Concerns;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesBusinessMembers
{
    protected function isMember(User $user, Business $business): bool
    {
        return $user->is_active
            && $business->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check the model belongs to the supplied business. When no business is
     * supplied, fall back to the model's own business for class-based checks.
     */
    protected function ownsModel(User $user, Model $model, ?Business $business = null): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($business instanceof Business) {
            return (int) $model->business_id === (int) $business->id
                && $this->isMember($user, $business);
        }

        $modelBusiness = $model->relationLoaded('business')
            ? $model->business
            : $model->business()->first();

        return $modelBusiness instanceof Business
            && $this->isMember($user, $modelBusiness);
    }
}
