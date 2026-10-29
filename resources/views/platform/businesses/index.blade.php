@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Businesses</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('platform.businesses.create') }}" class="btn btn-sm btn-primary">New Business</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('platform.businesses.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search businesses..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
                <a href="{{ route('platform.businesses.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($businesses as $business)
                    <tr>
                        <td>{{ $business->name }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $business->type }}</span>
                        </td>
                        <td>{{ $business->email }}</td>
                        <td>{{ $business->phone }}</td>
                        <td>
                            @if($business->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $business->creator->name }}</td>
                        <td>
                            <a href="{{ route('platform.businesses.show', $business) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('platform.businesses.edit', $business) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('platform.businesses.destroy', $business) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No businesses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $businesses->links() }}
    </div>
</div>
@endsection