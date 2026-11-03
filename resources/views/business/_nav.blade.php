@php
    $links = [
        ['label' => 'POS', 'route' => 'business.pos.index', 'icon' => 'bi-cart-check'],
        ['label' => 'Sales', 'route' => 'business.sales.index', 'icon' => 'bi-receipt'],
        ['label' => 'Quotations', 'route' => 'business.quotations.index', 'icon' => 'bi-file-earmark-text'],
        ['label' => 'Purchases', 'route' => 'business.purchases.index', 'icon' => 'bi-bag-check'],
        ['label' => 'Suppliers', 'route' => 'business.suppliers.index', 'icon' => 'bi-truck'],
        ['label' => 'Customers', 'route' => 'business.customers.index', 'icon' => 'bi-people'],
        ['label' => 'Expenses', 'route' => 'business.expenses.index', 'icon' => 'bi-cash-stack'],
        ['label' => 'Reports', 'route' => 'business.reports.index', 'icon' => 'bi-bar-chart'],
        ['label' => 'Cash Register', 'route' => 'business.cash-registers.index', 'icon' => 'bi-cash'],
        ['label' => 'Branches', 'route' => 'business.branches.index', 'icon' => 'bi-diagram-3'],
        ['label' => 'Products', 'route' => 'business.products.index', 'icon' => 'bi-box'],
        ['label' => 'Services', 'route' => 'business.services.index', 'icon' => 'bi-wrench'],
        ['label' => 'Categories', 'route' => 'business.categories.index', 'icon' => 'bi-tags'],
        ['label' => 'Units', 'route' => 'business.units.index', 'icon' => 'bi-rulers'],
        ['label' => 'Inventory', 'route' => 'business.inventory.index', 'icon' => 'bi-boxes'],
        ['label' => 'Locations', 'route' => 'business.stock-locations.index', 'icon' => 'bi-geo-alt'],
        ['label' => 'Settings', 'route' => 'business.settings.index', 'icon' => 'bi-gear'],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-building"></i> {{ $business->name }}</h4>
        <small class="text-muted">Business Management</small>
    </div>
    <nav class="nav nav-pills flex-wrap gap-1">
        @foreach ($links as $link)
            @if (Route::has($link['route']))
                <a class="nav-link {{ request()->routeIs($link['route'] . '*') ? 'active' : '' }}"
                   href="{{ route($link['route'], $business) }}">
                    <i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
</div>
