@extends('layouts.app')

@section('title', 'Recurring Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Recurring Invoices</h4>
        <p class="text-muted mb-0">Automate recurring billing</p>
    </div>
    <a href="{{ route('business.recurring-invoices.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Recurring</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Recurring #</th><th>Customer</th><th>Frequency</th><th>Next Date</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recurring as $rec)
                        <tr>
                            <td class="fw-medium">{{ $rec->recurring_number }}</td>
                            <td>{{ $rec->customer->full_name }}</td>
                            <td>{{ ucfirst($rec->frequency) }}</td>
                            <td>{{ $rec->next_date->format('d M Y') }}</td>
                            <td>{{ number_format($rec->total_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $rec->status === 'active' ? 'success' : 'secondary' }}">{{ $rec->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No recurring invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($recurring->hasPages())<div class="card-footer">{{ $recurring->links() }}</div>@endif
</div>
@endsection
