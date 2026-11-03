@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Invoices</h4>
        <p class="text-muted mb-0">All sales invoices</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="fw-medium">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->created_at->format('d M Y') }}</td>
                            <td>{{ $invoice->customer->full_name ?? 'Walk-in' }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            <td>{{ number_format($invoice->amount_paid, 2) }}</td>
                            <td>{{ number_format($invoice->amount_due, 2) }}</td>
                            <td><span class="badge bg-{{ $invoice->status === 'completed' ? 'success' : 'warning' }}">{{ $invoice->status }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('business.invoices.show', [$business, $invoice]) }}" class="btn btn-sm btn-light">View</a>
                                <a href="{{ route('business.invoices.print', [$business, $invoice]) }}" class="btn btn-sm btn-primary" target="_blank">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
