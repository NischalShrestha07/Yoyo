@extends('layouts.app')

@section('title', 'Products')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Products</h5>
        <a href="{{ route('business.products.create', $business) }}" class="btn btn-primary btn-sm">New product</a>
    </div>

    <form method="GET" action="{{ route('business.products.index', $business) }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                   placeholder="Search name, SKU or barcode">
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select form-select-sm">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm">
                <option value="">All types</option>
                @foreach (['physical', 'composite', 'ingredient'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="is_active" class="form-select form-select-sm">
                <option value="">Any status</option>
                <option value="1" @selected(request('is_active') === '1')>Active</option>
                <option value="0" @selected(request('is_active') === '0')>Archived</option>
            </select>
        </div>
        <div class="col-md-1 d-grid">
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>SKU / Barcode</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th class="text-end">Cost</th>
                    <th class="text-end">Price</th>
                    <th class="text-center">Stock</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <a href="{{ route('business.products.show', [$business, $product]) }}">
                                {{ $product->name }}
                            </a>
                            @unless ($product->is_active)
                                <span class="badge bg-secondary">Archived</span>
                            @endunless
                        </td>
                        <td>
                            <code>{{ $product->sku }}</code>
                            @if ($product->barcode)
                                <div class="small text-muted">{{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td><span class="badge bg-info text-dark">{{ $product->type }}</span></td>
                        <td class="text-end">{{ number_format((float) $product->purchase_cost, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $product->selling_price, 2) }}</td>
                        <td class="text-center">
                            @if (! $product->track_inventory)
                                <span class="badge bg-light text-dark border">Not tracked</span>
                            @else
                                @php
                                    $stock = (int) ($product->stock_on_hand ?? 0);
                                    $badge = $stock <= 0 ? 'danger' : ($stock <= (int) $product->min_stock_level ? 'warning text-dark' : 'success');
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $stock }}</span>
                                @if ($product->min_stock_level > 0)
                                    <div class="small text-muted">min {{ $product->min_stock_level }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('business.products.edit', [$business, $product]) }}"
                               class="btn btn-outline-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
@endsection
