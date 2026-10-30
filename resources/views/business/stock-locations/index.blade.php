@extends('layouts.app')

@section('title', 'Stock locations')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Stock locations</h5>
        <a href="{{ route('business.stock-locations.create', $business) }}" class="btn btn-primary btn-sm">New location</a>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th class="text-center">Balances</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($locations as $location)
                    <tr>
                        <td>
                            <a href="{{ route('business.stock-locations.show', [$business, $location]) }}">
                                {{ $location->name }}
                            </a>
                            @if ($location->is_default)
                                <span class="badge bg-primary">Default</span>
                            @endif
                        </td>
                        <td><code>{{ $location->code }}</code></td>
                        <td class="text-muted small">{{ $location->address ?? '—' }}</td>
                        <td class="text-center">{{ $location->stock_balances_count }}</td>
                        <td>
                            <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }}">
                                {{ $location->is_active ? 'Active' : 'Archived' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('business.stock-locations.edit', [$business, $location]) }}"
                               class="btn btn-outline-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No stock locations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $locations->links() }}
@endsection
