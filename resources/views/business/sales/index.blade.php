@extends('layouts.app')

@section('title', 'Sales History')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Sales History</span>
        <a href="{{ route('business.pos.index', $business) }}" class="btn btn-sm btn-primary">New Sale (POS)</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Invoice number" value="{{ request('search') }}"></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" @selected(request('status')=='completed')>Completed</option>
                    <option value="partial" @selected(request('status')=='partial')>Partial</option>
                    <option value="refunded" @selected(request('status')=='refunded')>Refunded</option>
                    <option value="cancelled" @selected(request('status')=='cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
            <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
            <div class="col-md-2"><button class="btn btn-secondary">Filter</button></div>
        </form>
        <table class="table table-striped">
            <thead>
                <tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th><th>Paid</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->created_at }}</td>
                        <td>{{ $sale->customer->full_name ?? 'Walk-in' }}</td>
                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                        <td>{{ number_format($sale->amount_paid, 2) }}</td>
                        <td>{{ $sale->status }}</td>
                        <td><a href="{{ route('business.sales.show', [$business, $sale]) }}" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $sales->links() }}
    </div>
</div>
@endsection
