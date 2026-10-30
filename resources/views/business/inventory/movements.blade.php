@extends('layouts.app')

@section('title', 'Stock movements')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Stock movement history</h5>
        <a href="{{ route('business.inventory.index', $business) }}" class="btn btn-outline-secondary btn-sm">Back to inventory</a>
    </div>

    <form method="GET" action="{{ route('business.inventory.movements', $business) }}" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="product_id" class="form-select form-select-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm">
                <option value="">All types</option>
                @foreach (['receipt', 'adjustment', 'transfer_out', 'transfer_in', 'sale', 'return', 'damage', 'reversal'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="stock_location_id" class="form-select form-select-sm">
                <option value="">All locations</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((int) request('stock_location_id') === $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>When</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Product</th>
                    <th>Location</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit cost</th>
                    <th>Reference</th>
                    <th>Actor</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td class="text-nowrap">{{ $movement->occurred_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $movement->type }}</span>
                            @if ($movement->reverses_movement_id)
                                <div class="small text-muted">reverses #{{ $movement->reverses_movement_id }}</div>
                            @endif
                        </td>
                        <td>{{ $movement->reason }}</td>
                        <td>
                            {{ $movement->product?->name }}
                            @if ($movement->productVariant)
                                <div class="small text-muted">{{ $movement->productVariant->name }}</div>
                            @endif
                        </td>
                        <td>{{ $movement->stockLocation?->name }}</td>
                        <td class="text-end {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="text-end">{{ $movement->unit_cost !== null ? number_format((float) $movement->unit_cost, 2) : '—' }}</td>
                        <td><code class="small">{{ $movement->reference ?? '—' }}</code></td>
                        <td>{{ $movement->user?->name ?? 'System' }}</td>
                        <td class="text-end">
                            @if (! $movement->reversal && ! $movement->reverses_movement_id
                                && ! in_array($movement->type, ['transfer_in', 'transfer_out', 'reversal'], true))
                                <form method="POST"
                                      action="{{ route('business.inventory.movements.reverse', [$business, $movement]) }}">
                                    @csrf
                                    <button class="btn btn-outline-danger btn-sm">Reverse</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No movements found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $movements->links() }}
@endsection
