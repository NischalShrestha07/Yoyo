@extends('layouts.app')

@section('title', 'Taxes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Taxes</h4>
        <p class="text-muted mb-0">Manage tax rates</p>
    </div>
    <a href="{{ route('business.taxes.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Tax</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Name</th><th>Code</th><th>Rate</th><th>Type</th><th>Default</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                        <tr>
                            <td class="fw-medium">{{ $tax->name }}</td>
                            <td>{{ $tax->code }}</td>
                            <td>{{ $tax->rate }}{{ $tax->type === 'percentage' ? '%' : '' }}</td>
                            <td>{{ $tax->type }}</td>
                            <td>{{ $tax->is_default ? 'Yes' : 'No' }}</td>
                            <td><span class="badge bg-{{ $tax->is_active ? 'success' : 'secondary' }}">{{ $tax->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No taxes found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($taxes->hasPages())<div class="card-footer">{{ $taxes->links() }}</div>@endif
</div>
@endsection
