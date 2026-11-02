@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Sales Report - Total: {{ number_format($total, 2) }}</span>
        <a href="{{ route('business.reports.index', $business) }}" class="btn btn-sm btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
            <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
        </form>
        <table class="table table-striped">
            <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                        <td>{{ $sale->customer->full_name ?? 'Walk-in' }}</td>
                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                        <td>{{ $sale->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $sales->links() }}
    </div>
</div>
@endsection
