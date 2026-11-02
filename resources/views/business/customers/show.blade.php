@extends('layouts.app')

@section('title', 'Customer')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header">{{ $customer->full_name }}</div>
    <div class="card-body">
        <p>Email: {{ $customer->email }}</p>
        <p>Phone: {{ $customer->phone }}</p>
        <p>Address: {{ $customer->address }}</p>
        <h5>Sales</h5>
        <ul>
            @foreach($customer->sales as $sale)
                <li><a href="{{ route('business.sales.show', [$business, $sale]) }}">{{ $sale->invoice_number }}</a> - {{ $sale->total_amount }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
