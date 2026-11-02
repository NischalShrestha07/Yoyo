@extends('layouts.app')

@section('title', 'Add Branch')

@section('content')
@include('business._nav')
<div class="card col-md-6">
    <div class="card-header">Add Branch</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.branches.store', $business) }}">
            @csrf
            <div class="mb-2"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="mb-2"><label class="form-label">Address</label><textarea name="address" class="form-control"></textarea></div>
            <div class="form-check mb-2"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" class="form-check-input"><label class="form-check-label">Set as default</label></div>
            <div class="form-check mb-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked><label class="form-check-label">Active</label></div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
