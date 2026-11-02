@extends('layouts.app')

@section('title', 'Purchase Order')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>PO {{ $purchaseOrder->po_number }} - {{ $purchaseOrder->status }}</span>
        @if($purchaseOrder->status !== 'received')
            <form method="POST" action="{{ route('business.purchases.receive', [$business, $purchaseOrder]) }}">
                @csrf
                <button class="btn btn-sm btn-success"><i class="bi bi-check"></i> Receive & Update Stock</button>
            </form>
        @endif
    </div>
    <div class="card-body">
        <p>Supplier: {{ $purchaseOrder->supplier->name ?? '-' }}</p>
        <p>Total: {{ number_format($purchaseOrder->total_amount, 2) }}</p>
        <table class="table">
            <thead><tr><th>Product</th><th>Qty</th><th>Unit Cost</th><th>Line Total</th></tr></thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr><td>{{ $item->product->name ?? '-' }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_cost, 2) }}</td><td>{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
