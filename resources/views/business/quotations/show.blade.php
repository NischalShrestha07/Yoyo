@extends('layouts.app')

@section('title', 'Quotation')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Quote {{ $quotation->quote_number }} - {{ $quotation->status }}</span>
        <form method="POST" action="{{ route('business.quotations.convert', [$business, $quotation]) }}">
            @csrf
            <button class="btn btn-sm btn-success"><i class="bi bi-cart-check"></i> Convert to Sale (POS)</button>
        </form>
    </div>
    <div class="card-body">
        <p>Customer: {{ $quotation->customer->full_name ?? '-' }}</p>
        <p>Total: {{ number_format($quotation->total_amount, 2) }}</p>
        <table class="table">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($quotation->items as $item)
                    <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
