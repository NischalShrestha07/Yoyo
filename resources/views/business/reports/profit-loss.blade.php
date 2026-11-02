@extends('layouts.app')

@section('title', 'Profit & Loss')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header"><i class="bi bi-graph-up"></i> Profit & Loss Report</div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ \Carbon\Carbon::parse($from)->format('Y-m-d') }}"></div>
            <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ \Carbon\Carbon::parse($to)->format('Y-m-d') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
        </form>
        <table class="table table-bordered">
            <tr><td>Sales Revenue</td><td class="text-end">{{ number_format($sales, 2) }}</td></tr>
            <tr><td>Purchases (Cost)</td><td class="text-end">- {{ number_format($purchases, 2) }}</td></tr>
            <tr><td>Expenses</td><td class="text-end">- {{ number_format($expenses, 2) }}</td></tr>
            <tr class="table-active"><td><strong>Net Profit/Loss</strong></td><td class="text-end"><strong>{{ number_format($profit, 2) }}</strong></td></tr>
        </table>
    </div>
</div>
@endsection
