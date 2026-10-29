@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">{{ $business->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('platform.businesses.edit', $business) }}" class="btn btn-sm btn-warning">Edit Business</a>
        <a href="{{ route('platform.memberships.index', $business) }}" class="btn btn-sm btn-info">Manage Members</a>
        <a href="{{ route('platform.modules.edit', $business) }}" class="btn btn-sm btn-primary">Manage Modules</a>
        <a href="{{ route('platform.settings.business', $business) }}" class="btn btn-sm btn-secondary">Settings</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Business Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Type:</strong> {{ $business->type }}</p>
                <p><strong>Email:</strong> {{ $business->email }}</p>
                <p><strong>Phone:</strong> {{ $business->phone }}</p>
                <p><strong>Address:</strong> {{ $business->address }}</p>
                <p><strong>Tax ID:</strong> {{ $business->tax_id }}</p>
                <p><strong>Status:</strong> 
                    @if($business->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </p>
                <p><strong>Created:</strong> {{ $business->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <p><strong>Members:</strong> {{ $business->memberships_count }}</p>
                <p><strong>Active Modules:</strong> {{ $business->modules()->wherePivot('enabled', true)->count() }}</p>
                <p><strong>Total Modules:</strong> {{ $business->modules_count }}</p>
                <p><strong>Roles:</strong> {{ $business->roles_count }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Members</h5>
            </div>
            <div class="card-body">
                @forelse ($business->memberships()->latest()->take(5)->get() as $membership)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="mb-0">{{ $membership->user->name }}</p>
                            <small class="text-muted">{{ $membership->status }}</small>
                        </div>
                        <span class="badge bg-{{ $membership->status === 'active' ? 'success' : 'secondary' }}">
                            {{ $membership->status }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted">No members found.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Enabled Modules</h5>
            </div>
            <div class="card-body">
                @forelse ($business->modules()->wherePivot('enabled', true)->get() as $module)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="mb-0">{{ $module->name }}</p>
                            <small class="text-muted">{{ $module->group }}</small>
                        </div>
                        <span class="badge bg-success">Enabled</span>
                    </div>
                @empty
                    <p class="text-muted">No modules enabled.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Business Activity</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Recent activity logs will be displayed here.</p>
    </div>
</div>
@endsection