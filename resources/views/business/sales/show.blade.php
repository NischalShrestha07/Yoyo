@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
@include('business._nav')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">Sale {{ $sale->invoice_number }} - {{ $sale->status }}</div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->line_total,2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end">
                    <div>Subtotal: {{ number_format($sale->subtotal,2) }}</div>
                    <div>Discount: {{ number_format($sale->discount_amount,2) }}</div>
                    <div>Tax: {{ number_format($sale->tax_amount,2) }}</div>
                    <div class="fw-bold">Total: {{ number_format($sale->total_amount,2) }}</div>
                    <div>Paid: {{ number_format($sale->amount_paid,2) }}</div>
                    <div>Due: {{ number_format($sale->amount_due,2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Actions</div>
            <div class="card-body">
                <a href="{{ route('business.pos.receipt', [$business, $sale]) }}" class="btn btn-secondary w-100 mb-2">View Receipt</a>
                @if($sale->status !== 'cancelled' && $sale->status !== 'refunded')
                    <form method="POST" action="{{ route('business.sales.cancel', [$business, $sale]) }}" class="mb-2">
                        @csrf
                        <input type="text" name="reason" class="form-control mb-1" placeholder="Reason">
                        <button class="btn btn-warning w-100">Cancel Sale</button>
                    </form>
                    <form method="POST" action="{{ route('business.sales.return', [$business, $sale]) }}">
                        @csrf
                        @foreach($sale->items as $idx => $item)
                            <input type="hidden" name="items[{{ $idx }}][sale_item_id]" value="{{ $item->id }}">
                            <div class="mb-1">
                                <label class="form-label">{{ $item->product_name }} (max {{ $item->quantity }})</label>
                                <input type="number" name="items[{{ $idx }}][quantity]" class="form-control" value="0" min="0" max="{{ $item->quantity }}">
                            </div>
                        @endforeach
                        <input type="text" name="reason" class="form-control mb-1" placeholder="Return reason">
                        <button class="btn btn-danger w-100">Process Return/Refund</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">Payments</div>
            <div class="card-body">
                @foreach($sale->payments as $payment)
                    <div>{{ $payment->method }}: {{ number_format($payment->amount,2) }} ({{ $payment->status }})</div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
