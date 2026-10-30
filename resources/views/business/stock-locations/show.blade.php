@extends('layouts.app')

@section('title', $stockLocation->name)

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1">{{ $stockLocation->name }}</h5>
            <div class="text-muted">
                <code>{{ $stockLocation->code }}</code>
                @if ($stockLocation->is_default) · <span class="badge bg-primary">Default</span> @endif
                · <span class="badge bg-{{ $stockLocation->is_active ? 'success' : 'secondary' }}">
                    {{ $stockLocation->is_active ? 'Active' : 'Archived' }}
                </span>
            </div>
        </div>
        <a href="{{ route('business.stock-locations.edit', [$business, $stockLocation]) }}"
           class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>

    @if ($stockLocation->address)
        <p class="text-muted">{{ $stockLocation->address }}</p>
    @endif

    <h6>Stock at this location</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Reserved</th>
                    <th class="text-end">Available</th>
                    <th class="text-end">Average cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockLocation->stockBalances as $balance)
                    <tr>
                        <td>{{ $balance->product?->name }}</td>
                        <td>{{ $balance->productVariant?->name ?? '—' }}</td>
                        <td class="text-end">{{ $balance->quantity }}</td>
                        <td class="text-end">{{ $balance->reserved_quantity }}</td>
                        <td class="text-end">{{ $balance->available_quantity }}</td>
                        <td class="text-end">{{ number_format((float) $balance->average_cost, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No stock at this location.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
