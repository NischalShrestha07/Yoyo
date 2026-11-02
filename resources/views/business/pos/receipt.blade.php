@extends('layouts.app')

@section('title', 'Receipt')

@section('content')
@include('business._nav')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Receipt</span>
                <a href="{{ route('business.pos.print', [$business, $sale]) }}" class="btn btn-sm btn-secondary" target="_blank">Print</a>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>{{ $business->name }}</strong><br>
                    Invoice: {{ $sale->invoice_number }}<br>
                    Date: {{ $sale->created_at }}<br>
                    Cashier: {{ $sale->user->name ?? '-' }}<br>
                    Customer: {{ $sale->customer->full_name ?? 'Walk-in' }}
                </div>
                <table class="table table-sm">
                    <thead>
                        <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end">
                    <div>Subtotal: {{ number_format($sale->subtotal, 2) }}</div>
                    <div>Discount: {{ number_format($sale->discount_amount, 2) }}</div>
                    <div>Tax: {{ number_format($sale->tax_amount, 2) }}</div>
                    <div class="fw-bold">Total: {{ number_format($sale->total_amount, 2) }}</div>
                    <div>Paid: {{ number_format($sale->amount_paid, 2) }}</div>
                    <div>Due: {{ number_format($sale->amount_due, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="mt-2">
            <a href="{{ route('business.pos.index', $business) }}" class="btn btn-primary">New Sale</a>
            <a href="{{ route('business.sales.index', $business) }}" class="btn btn-secondary">Sales History</a>
        </div>
    </div>
</div>
@endsection
