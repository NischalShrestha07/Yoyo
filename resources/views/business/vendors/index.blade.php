@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Vendors</h4>
        <p class="text-muted mb-0">Manage your vendors</p>
    </div>
    <a href="{{ route('business.vendors.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Vendor</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Balance</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td class="fw-medium">{{ $vendor->name }}</td>
                            <td>{{ $vendor->email }}</td>
                            <td>{{ $vendor->phone }}</td>
                            <td>{{ number_format($vendor->balance, 2) }}</td>
                            <td><span class="badge bg-{{ $vendor->is_active ? 'success' : 'secondary' }}">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end"><a href="{{ route('business.vendors.show', [$business, $vendor]) }}" class="btn btn-sm btn-light">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No vendors found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($vendors->hasPages())<div class="card-footer">{{ $vendors->links() }}</div>@endif
</div>
@endsection
