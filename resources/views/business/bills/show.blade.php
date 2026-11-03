@extends('layouts.app')

@section('title', 'Bill '.$bill->bill_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Bill {{ $bill->bill_number }}</h4>
        <p class="text-muted mb-0">{{ $bill->bill_date->format('d M Y') }}</p>
    </div>
    <a href="{{ route('business.bills.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Vendor</h6>
                <p class="mb-1 fw-medium">{{ $bill->vendor->name }}</p>
                <p class="text-muted mb-1">{{ $bill->vendor->email }}</p>
                <p class="text-muted mb-0">{{ $bill->vendor->phone }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">{{ $bill->status }}</span></p>
                <p class="mb-1"><strong>Due Date:</strong> {{ $bill->due_date?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table">
                <thead class="table-light"><tr><th>Item</th><th>Qty</th><th>Price</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @foreach($bill->items as $item)
                        <tr><td>{{ $item->item_name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td class="text-end">{{ number_format($item->line_total, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><td>Subtotal</td><td class="text-end">{{ number_format($bill->subtotal, 2) }}</td></tr>
                    <tr class="border-top"><td class="fw-bold">Total</td><td class="text-end fw-bold">{{ number_format($bill->total_amount, 2) }}</td></tr>
                    <tr><td>Paid</td><td class="text-end">{{ number_format($bill->amount_paid, 2) }}</td></tr>
                    <tr><td>Due</td><td class="text-end">{{ number_format($bill->amount_due, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
