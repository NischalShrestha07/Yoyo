@extends('layouts.app')

@section('title', 'Reports')

@section('content')
@include('business._nav')
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Sales Today</h6>
                <h4>{{ number_format($salesToday, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Sales This Month</h6>
                <h4>{{ number_format($salesMonth, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Expenses This Month</h6>
                <h4>{{ number_format($expensesMonth, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Low Stock Items</h6>
                <h4>{{ $lowStock }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-bar-chart"></i> Reports</span>
                <div>
                    <a href="{{ route('business.reports.sales', $business) }}" class="btn btn-sm btn-primary">Sales Report</a>
                    <a href="{{ route('business.reports.inventory', $business) }}" class="btn btn-sm btn-info">Inventory Report</a>
                    <a href="{{ route('business.reports.profit-loss', $business) }}" class="btn btn-sm btn-success">P&L Report</a>
                </div>
            </div>
            <div class="card-body">
                <h6>Recent Sales</h6>
                <table class="table table-sm">
                    <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('business.sales.show', [$business, $sale]) }}">{{ $sale->invoice_number }}</a></td>
                                <td>{{ $sale->customer->full_name ?? 'Walk-in' }}</td>
                                <td>{{ number_format($sale->total_amount, 2) }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Quick Stats</div>
            <div class="card-body">
                <p>Total Sales: <strong>{{ $totalSales }}</strong></p>
                <p>Purchases This Month: <strong>{{ number_format($purchasesMonth, 2) }}</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection
