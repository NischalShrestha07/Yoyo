@extends('layouts.app')

@section('title', 'Bills')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Bills</h4>
        <p class="text-muted mb-0">Vendor bills & payables</p>
    </div>
    <a href="{{ route('business.bills.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Bill</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Bill #</th><th>Vendor</th><th>Date</th><th>Due Date</th><th>Total</th><th>Due</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        <tr>
                            <td class="fw-medium">{{ $bill->bill_number }}</td>
                            <td>{{ $bill->vendor->name }}</td>
                            <td>{{ $bill->bill_date->format('d M Y') }}</td>
                            <td>{{ $bill->due_date?->format('d M Y') }}</td>
                            <td>{{ number_format($bill->total_amount, 2) }}</td>
                            <td>{{ number_format($bill->amount_due, 2) }}</td>
                            <td><span class="badge bg-{{ $bill->status === 'paid' ? 'success' : ($bill->status === 'overdue' ? 'danger' : 'warning') }}">{{ $bill->status }}</span></td>
                            <td class="text-end"><a href="{{ route('business.bills.show', [$business, $bill]) }}" class="btn btn-sm btn-light">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No bills found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($bills->hasPages())<div class="card-footer">{{ $bills->links() }}</div>@endif
</div>
@endsection
