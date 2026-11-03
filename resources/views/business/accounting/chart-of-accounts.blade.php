@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Chart of Accounts</h4>
        <p class="text-muted mb-0">Manage your account structure</p>
    </div>
    <a href="{{ route('business.accounting.dashboard', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Code</th><th>Account Name</th><th>Type</th><th>Parent</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td class="fw-medium">{{ $account->code }}</td>
                            <td>{{ $account->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst($account->type) }}</span></td>
                            <td>{{ $account->parent->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No accounts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
