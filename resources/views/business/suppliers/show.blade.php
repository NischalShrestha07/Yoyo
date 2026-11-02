@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header">{{ $supplier->name }}</div>
    <div class="card-body">
        <p>Email: {{ $supplier->email }}</p>
        <p>Phone: {{ $supplier->phone }}</p>
        <p>Address: {{ $supplier->address }}</p>
        <p>Tax ID: {{ $supplier->tax_id }}</p>
    </div>
</div>
@endsection
