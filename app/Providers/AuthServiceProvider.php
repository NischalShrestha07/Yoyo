<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use App\Models\Category;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Policies\BusinessPolicy;
use App\Policies\BusinessMembership;
use App\Models\BusinessMembership as BusinessMembershipModel;
use App\Models\Setting as SettingModel;
use App\Models\Module as ModuleModel;
use App\Policies\MembershipPolicy;
use App\Policies\SettingPolicy;
use App\Policies\ModulePolicy;
use App\Policies\ProductPolicy;
use App\Policies\ServicePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\StockLocationPolicy;
use App\Policies\StockMovementPolicy;
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
        BusinessMembershipModel::class => MembershipPolicy::class,
        SettingModel::class => SettingPolicy::class,
        ModuleModel::class => ModulePolicy::class,
        Product::class => ProductPolicy::class,
        Service::class => ServicePolicy::class,
        Category::class => CategoryPolicy::class,
        StockLocation::class => StockLocationPolicy::class,
        StockMovement::class => StockMovementPolicy::class,
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