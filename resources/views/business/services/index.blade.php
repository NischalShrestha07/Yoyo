@extends('layouts.app')

@section('title', 'Services')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Services</h5>
        <a href="{{ route('business.services.create', $business) }}" class="btn btn-primary btn-sm">New service</a>
    </div>

    <form method="GET" action="{{ route('business.services.index', $business) }}" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                   placeholder="Search name or service code">
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
            <select name="is_active" class="form-select form-select-sm">
                <option value="">Any status</option>
                <option value="1" @selected(request('is_active') === '1')>Active</option>
                <option value="0" @selected(request('is_active') === '0')>Archived</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Category</th>
                    <th class="text-end">Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td><a href="{{ route('business.services.show', [$business, $service]) }}">{{ $service->name }}</a></td>
                        <td><code>{{ $service->code }}</code></td>
                        <td>{{ $service->category?->name ?? '—' }}</td>
                        <td class="text-end">{{ number_format((float) $service->selling_price, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                {{ $service->is_active ? 'Active' : 'Archived' }}
                            </span>
                            <span class="badge bg-light text-dark border">No stock</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('business.services.edit', [$business, $service]) }}"
                               class="btn btn-outline-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No services found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $services->links() }}
@endsection
