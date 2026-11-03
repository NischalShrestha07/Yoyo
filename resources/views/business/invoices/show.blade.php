@extends('layouts.app')

@section('title', 'Invoice '.$sale->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Invoice {{ $sale->invoice_number }}</h4>
        <p class="text-muted mb-0">{{ $sale->created_at->format('d M Y H:i') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('business.invoices.index', $business) }}" class="btn btn-light">Back</a>
        <a href="{{ route('business.invoices.print', [$business, $sale]) }}" class="btn btn-primary" target="_blank"><i class="bi bi-printer"></i> Print</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>{{ $sale->business->name }}</h5>
                <p class="text-muted mb-1">{{ $sale->business->email }}</p>
                <p class="text-muted mb-0">{{ $sale->business->phone }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <h4 class="text-primary">INVOICE</h4>
                <p class="mb-1"><strong>No:</strong> {{ $sale->invoice_number }}</p>
                <p class="mb-1"><strong>Date:</strong> {{ $sale->created_at->format('d M Y') }}</p>
                @if($sale->due_date)<p class="mb-0"><strong>Due:</strong> {{ $sale->due_date->format('d M Y') }}</p>@endif
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Bill To</h6>
                <p class="mb-1 fw-medium">{{ $sale->customer->full_name ?? 'Walk-in Customer' }}</p>
                @if($sale->customer->email)<p class="text-muted mb-1">{{ $sale->customer->email }}</p>@endif
                @if($sale->customer->phone)<p class="text-muted mb-0">{{ $sale->customer->phone }}</p>@endif
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table">
                <thead class="table-light">
                    <tr><th>#</th><th>Item</th><th>Qty</th><th>Price</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->name ?? $item->description }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><td>Subtotal</td><td class="text-end">{{ number_format($sale->subtotal, 2) }}</td></tr>
                    @if($sale->discount_amount > 0)<tr><td>Discount</td><td class="text-end">-{{ number_format($sale->discount_amount, 2) }}</td></tr>@endif
                    @if($sale->tax_amount > 0)<tr><td>Tax</td><td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td></tr>@endif
                    <tr class="border-top"><td class="fw-bold">Total</td><td class="text-end fw-bold">{{ number_format($sale->total_amount, 2) }}</td></tr>
                    <tr><td>Paid</td><td class="text-end">{{ number_format($sale->amount_paid, 2) }}</td></tr>
                    <tr><td>Due</td><td class="text-end">{{ number_format($sale->amount_due, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
