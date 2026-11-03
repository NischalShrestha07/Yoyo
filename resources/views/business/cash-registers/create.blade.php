@extends('layouts.app')

@section('title', 'Open Cash Register')

@section('content')
@include('business._nav')
<div class="card col-md-6">
    <div class="card-header">Open Cash Register</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.cash-registers.store', $business) }}">
            @csrf
            <div class="mb-2"><label class="form-label">Register Name</label><input type="text" name="name" class="form-control" value="Main Cash" required></div>
            <div class="mb-2"><label class="form-label">Opening Balance</label><input type="number" step="0.01" name="opening_balance" class="form-control" value="0" required></div>
            <button class="btn btn-primary">Open Register</button>
        </form>
    </div>
</div>
@endsection
