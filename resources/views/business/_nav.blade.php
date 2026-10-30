@php
    $links = [
        ['label' => 'Products', 'route' => 'business.products.index'],
        ['label' => 'Services', 'route' => 'business.services.index'],
        ['label' => 'Categories', 'route' => 'business.categories.index'],
        ['label' => 'Units', 'route' => 'business.units.index'],
        ['label' => 'Inventory', 'route' => 'business.inventory.index'],
        ['label' => 'Locations', 'route' => 'business.stock-locations.index'],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">{{ $business->name }}</h4>
        <small class="text-muted">Catalog &amp; inventory workspace</small>
    </div>
    <ul class="nav nav-pills">
        @foreach ($links as $link)
            @if (Route::has($link['route']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs($link['route'] . '*') ? 'active' : '' }}"
                       href="{{ route($link['route'], $business) }}">{{ $link['label'] }}</a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
