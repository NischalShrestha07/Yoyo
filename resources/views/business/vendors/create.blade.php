@extends('layouts.app')

@section('title', 'Add Vendor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Add Vendor</h4>
    </div>
    <a href="{{ route('business.vendors.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('business.vendors.store', $business) }}">
            @csrf
            <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Tax Number</label><input type="text" name="tax_number" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="3"></textarea></div>
            <button class="btn btn-primary">Save Vendor</button>
        </form>
    </div>
</div>
@endsection
