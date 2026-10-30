@extends('layouts.app')

@section('title', 'Units of measurement')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Units of measurement</h5>
        <a href="{{ route('business.units.create', $business) }}" class="btn btn-primary btn-sm">New unit</a>
    </div>

    <form method="GET" action="{{ route('business.units.index', $business) }}" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                   placeholder="Search name or code">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary btn-sm">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td><a href="{{ route('business.units.show', [$business, $unit]) }}">{{ $unit->name }}</a></td>
                        <td><code>{{ $unit->code }}</code></td>
                        <td>{{ $unit->products_count }}</td>
                        <td>
                            <span class="badge bg-{{ $unit->is_active ? 'success' : 'secondary' }}">
                                {{ $unit->is_active ? 'Active' : 'Archived' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('business.units.edit', [$business, $unit]) }}"
                               class="btn btn-outline-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No units yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $units->links() }}
@endsection
