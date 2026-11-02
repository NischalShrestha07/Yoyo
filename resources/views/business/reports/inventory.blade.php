@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Inventory Report</span>
        <a href="{{ route('business.reports.index', $business) }}" class="btn btn-sm btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Product</th><th>SKU</th><th>Current Stock</th><th>Min Stock</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->total_stock ?? 0 }}</td>
                        <td>{{ $product->min_stock_level }}</td>
                        <td>
                            @if(($product->total_stock ?? 0) <= $product->min_stock_level && $product->min_stock_level > 0)
                                <span class="badge bg-danger">Low Stock</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
