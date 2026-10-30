@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Categories</h5>
        <a href="{{ route('business.categories.create', $business) }}" class="btn btn-primary btn-sm">New category</a>
    </div>

    <form method="GET" action="{{ route('business.categories.index', $business) }}" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                   placeholder="Search categories">
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
                    <th>Parent</th>
                    <th>Products</th>
                    <th>Services</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            <a href="{{ route('business.categories.show', [$business, $category]) }}">
                                {{ $category->name }}
                            </a>
                        </td>
                        <td>{{ $category->parent?->name ?? '—' }}</td>
                        <td>{{ $category->products()->count() }}</td>
                        <td>{{ $category->services()->count() }}</td>
                        <td>
                            <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Archived' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('business.categories.edit', [$business, $category]) }}"
                               class="btn btn-outline-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $categories->links() }}
@endsection
