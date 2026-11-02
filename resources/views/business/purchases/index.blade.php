@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-bag-check"></i> Purchase Orders</span>
        <a href="{{ route('business.purchases.create', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> New PO</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>PO Number</th><th>Supplier</th><th>Status</th><th>Total</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->po_number }}</td>
                        <td>{{ $order->supplier->name ?? '-' }}</td>
                        <td><span class="badge bg-{{ $order->status == 'received' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">{{ $order->status }}</span></td>
                        <td>{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td><a href="{{ route('business.purchases.show', [$business, $order]) }}" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
</div>
@endsection
