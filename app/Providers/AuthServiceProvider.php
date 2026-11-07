<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use App\Models\Category;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\UnitOfMeasurement;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Policies\BusinessPolicy;
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
use App\Policies\UnitOfMeasurementPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SalePolicy;
use App\Policies\SaleItemPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\SaleReturnPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseOrderItemPolicy;
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
        UnitOfMeasurement::class => UnitOfMeasurementPolicy::class,
        Customer::class => CustomerPolicy::class,
        Sale::class => SalePolicy::class,
        SaleItem::class => SaleItemPolicy::class,
        Payment::class => PaymentPolicy::class,
        SaleReturn::class => SaleReturnPolicy::class,
        Supplier::class => SupplierPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        PurchaseOrderItem::class => PurchaseOrderItemPolicy::class,
        \App\Models\Expense::class => \App\Policies\ExpensePolicy::class,
        \App\Models\Quotation::class => \App\Policies\QuotationPolicy::class,
        \App\Models\QuotationItem::class => \App\Policies\QuotationItemPolicy::class,
        \App\Models\Branch::class => \App\Policies\BranchPolicy::class,
        \App\Models\CashRegister::class => \App\Policies\CashRegisterPolicy::class,
        \App\Models\ChartOfAccount::class => \App\Policies\ChartOfAccountPolicy::class,
        \App\Models\JournalEntry::class => \App\Policies\JournalEntryPolicy::class,
        \App\Models\Vendor::class => \App\Policies\VendorPolicy::class,
        \App\Models\Bill::class => \App\Policies\BillPolicy::class,
        \App\Models\PaymentMade::class => \App\Policies\PaymentMadePolicy::class,
        \App\Models\CreditNote::class => \App\Policies\CreditNotePolicy::class,
        \App\Models\DebitNote::class => \App\Policies\DebitNotePolicy::class,
        \App\Models\Tax::class => \App\Policies\TaxPolicy::class,
        \App\Models\RecurringInvoice::class => \App\Policies\RecurringInvoicePolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
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
use App\Models\Expense;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Policies\ExpensePolicy;
use App\Policies\QuotationPolicy;
use App\Policies\QuotationItemPolicy;
