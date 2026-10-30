@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Inventory</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('business.inventory.receipt.form', $business) }}" class="btn btn-primary btn-sm">Receive stock</a>
            <a href="{{ route('business.inventory.adjustment.form', $business) }}" class="btn btn-outline-primary btn-sm">Adjust</a>
            @if ($config->multipleLocations())
                <a href="{{ route('business.inventory.transfer.form', $business) }}" class="btn btn-outline-primary btn-sm">Transfer</a>
            @endif
            <a href="{{ route('business.inventory.return.form', $business) }}" class="btn btn-outline-primary btn-sm">Return</a>
            <a href="{{ route('business.inventory.movements', $business) }}" class="btn btn-outline-secondary btn-sm">Movements</a>
            <a href="{{ route('business.inventory.settings', $business) }}" class="btn btn-outline-secondary btn-sm">Settings</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Valuation method</div>
                <div class="fs-5">{{ $valuationMethod === 'fifo' ? 'FIFO' : 'Weighted average' }}</div>
                <div class="text-muted small">Configured explicitly per business.</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Stock value{{ $selectedLocation ? ' at ' . $selectedLocation->name : '' }}</div>
                <div class="fs-4">{{ number_format($totalValuation, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">Low-stock products</div>
                <div class="fs-4 {{ $lowStock->isNotEmpty() ? 'text-warning' : '' }}">{{ $lowStock->count() }}</div>
            </div></div>
        </div>
    </div>

    @if ($lowStock->isNotEmpty())
        <div class="alert alert-warning">
            <strong>Low stock:</strong>
            @foreach ($lowStock as $product)
                <span class="badge bg-warning text-dark">
                    {{ $product->name }} ({{ (int) $product->total_stock }} / min {{ $product->min_stock_level }})
                </span>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('business.inventory.index', $business) }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="product_id" class="form-select form-select-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
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
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="low_stock" value="1"
                       id="low_stock" @checked(request('low_stock'))>
                <label class="form-check-label" for="low_stock">Low stock only</label>
            </div>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </div>
    </form>

    <div class="table-responsive mb-4">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Location</th>
                    <th class="text-end">On hand</th>
                    <th class="text-end">Reserved</th>
                    <th class="text-end">Available</th>
                    <th class="text-end">Average cost</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($balances as $balance)
                    @php
                        $available = $balance->quantity - $balance->reserved_quantity;
                        $min = (int) ($balance->product?->min_stock_level ?? 0);
                        $badge = $available <= 0 ? 'danger' : ($min > 0 && $available <= $min ? 'warning text-dark' : 'success');
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('business.products.show', [$business, $balance->product]) }}">
                                {{ $balance->product?->name }}
                            </a>
                        </td>
                        <td>{{ $balance->productVariant?->name ?? '—' }}</td>
                        <td>{{ $balance->stockLocation?->name }}</td>
                        <td class="text-end">{{ $balance->quantity }}</td>
                        <td class="text-end">{{ $balance->reserved_quantity }}</td>
                        <td class="text-end">{{ $available }}</td>
                        <td class="text-end">{{ number_format((float) $balance->average_cost, 2) }}</td>
                        <td class="text-center"><span class="badge bg-{{ $badge }}">{{ $available <= 0 ? 'Out' : 'In stock' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No stock balances yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $balances->links() }}

    @if ($config->reservationsEnabled())
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Active reservations</h6>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Location</th>
                        <th class="text-end">Quantity</th>
                        <th>Expires</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                        <tr>
                            <td><code>{{ $reservation->reference }}</code></td>
                            <td>{{ $reservation->product?->name }}</td>
                            <td>{{ $reservation->stockLocation?->name }}</td>
                            <td class="text-end">{{ $reservation->quantity }}</td>
                            <td>{{ $reservation->expires_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="text-end">
                                <form method="POST"
                                      action="{{ route('business.inventory.reservations.destroy', [$business, $reservation]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Release</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No active reservations.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('business.inventory.reservations.store', $business) }}" class="row g-2">
            @csrf
            <div class="col-md-3">
                <select name="product_id" class="form-select form-select-sm" required>
                    <option value="">Product…</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_location_id" class="form-select form-select-sm" required>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" min="1" name="quantity" class="form-control form-control-sm" placeholder="Quantity" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="reference" class="form-control form-control-sm" placeholder="Reference" required>
            </div>
            <div class="col-md-1">
                <input type="datetime-local" name="expires_at" class="form-control form-control-sm">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary btn-sm">Reserve</button>
            </div>
        </form>
    @endif
@endsection
