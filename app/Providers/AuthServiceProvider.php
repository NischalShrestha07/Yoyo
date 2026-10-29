<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\User;
use App\Policies\BusinessPolicy;
use App\Policies\PlatformSettingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Business::class => BusinessPolicy::class,
        // Add other policy mappings here
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register abilities
        Gate::define('manage-business', function (User $user, Business $business) {
            return $user->is_active && 
                   $business->users()->where('user_id', $user->id)->exists() &&
                   $business->memberships()->where('user_id', $user->id)
                       ->where('status', 'active')
                       ->whereHas('roles', function ($query) {
                           $query->where('name', 'admin');
                       })->exists();
        });
    }
}