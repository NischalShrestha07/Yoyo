@extends('layouts.platform')

@section('platform-content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Businesses</h5>
                <h2 class="text-primary">{{ $totalBusinesses }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Active Businesses</h5>
                <h2 class="text-success">{{ $activeBusinesses }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="text-info">{{ $totalUsers }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Businesses</h5>
        <a href="{{ route('platform.businesses.index') }}" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Members</th>
                        <th>Modules</th>
                        <th>Status</th>
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
                        <td>{{ $business->memberships_count }}</td>
                        <td>{{ $business->modules_count }}</td>
                        <td>
                            @if($business->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('platform.businesses.show', $business) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('platform.businesses.edit', $business) }}" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No businesses found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $businesses->links() }}
    </div>
</div>
@endsection