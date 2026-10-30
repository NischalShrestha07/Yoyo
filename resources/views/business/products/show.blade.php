@extends('layouts.app')

@section('title', $product->name)

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h5 class="mb-1">{{ $product->name }}</h5>
            <div class="text-muted">
                <code>{{ $product->sku }}</code>
                @if ($product->barcode) · {{ $product->barcode }} @endif
                · <span class="badge bg-info text-dark">{{ $product->type }}</span>
                @unless ($product->is_active) <span class="badge bg-secondary">Archived</span> @endunless
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('business.products.edit', [$business, $product]) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
            @if ($product->is_active)
                <form method="POST" action="{{ route('business.products.archive', [$business, $product]) }}">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">Archive</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Stock on hand</div>
                <div class="fs-4">
                    @if ($product->track_inventory)
                        {{ $currentStock }}
                    @else
                        <span class="fs-6">Not tracked</span>
                    @endif
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Valuation</div>
                <div class="fs-4">{{ number_format($valuation, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Purchase cost</div>
                <div class="fs-4">{{ number_format((float) $product->purchase_cost, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Selling price</div>
                <div class="fs-4">{{ number_format((float) $product->selling_price, 2) }}</div>
            </div></div>
        </div>
    </div>

    @if (! empty($product->images))
        <div class="d-flex gap-2 mb-4">
            @foreach ($product->images as $image)
                <img src="{{ Storage::disk('public')->url($image) }}" alt="" class="rounded border" style="height:96px">
            @endforeach
        </div>
    @endif

    @if ($product->description)
        <p>{{ $product->description }}</p>
    @endif

    @if ($product->has_variants)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Variants</h6>
            <a href="{{ route('business.products.variants.create', [$business, $product]) }}"
               class="btn btn-outline-primary btn-sm">Add variant</a>
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Variant</th>
                        <th>SKU</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->variants as $variant)
                        <tr>
                            <td>{{ $variant->name }}</td>
                            <td><code>{{ $variant->sku }}</code></td>
                            <td class="text-end">{{ number_format((float) $variant->purchase_cost, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $variant->selling_price, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $variant->is_active ? 'success' : 'secondary' }}">
                                    {{ $variant->is_active ? 'Active' : 'Archived' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('business.products.variants.edit', [$business, $product, $variant]) }}"
                                   class="btn btn-outline-secondary btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center py-3">No variants yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <h6>Recent stock movements</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>When</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Location</th>
                    <th class="text-end">Qty</th>
                    <th>Actor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($product->stockMovements as $movement)
                    <tr>
                        <td>{{ $movement->occurred_at?->format('Y-m-d H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $movement->type }}</span></td>
                        <td>{{ $movement->reason }}</td>
                        <td>{{ $movement->stockLocation?->name }}</td>
                        <td class="text-end {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td>{{ $movement->user?->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No movements recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('business.inventory.movements', ['business' => $business, 'product_id' => $product->id]) }}"
       class="btn btn-link px-0">View full movement history</a>
@endsection
