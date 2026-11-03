@extends('layouts.app')

@section('title', 'Payments Made')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Payments Made</h4>
        <p class="text-muted mb-0">Payments to vendors</p>
    </div>
    <a href="{{ route('business.payments-made.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Record Payment</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Payment #</th><th>Date</th><th>Vendor</th><th>Bill</th><th>Amount</th><th>Method</th><th>Reference</th></tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="fw-medium">{{ $payment->payment_number }}</td>
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>{{ $payment->vendor->name }}</td>
                            <td>{{ $payment->bill->bill_number ?? '-' }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->reference }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())<div class="card-footer">{{ $payments->links() }}</div>@endif
</div>
@endsection
