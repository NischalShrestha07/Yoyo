@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
@include('business._nav')
<div class="card col-md-6">
    <div class="card-header">Add Customer</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.customers.store', $business) }}">
            @csrf
            <div class="mb-2">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>
            <div class="form-check mb-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                <label class="form-check-label">Active</label>
            </div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
