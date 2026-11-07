@extends('layouts.app')

@section('title', 'Add Tax')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Add Tax</h4>
    </div>
    <a href="{{ route('business.taxes.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('business.taxes.store', $business) }}">
            @csrf
            <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control"></div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Rate *</label><input type="number" step="0.01" name="rate" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Type *</label>
                    <select name="type" class="form-select" required>
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed</option>
                    </select>
                </div>
            </div>
            <div class="form-check mb-2"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" class="form-check-input"><label class="form-check-label">Default Tax</label></div>
            <div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked><label class="form-check-label">Active</label></div>
            <button class="btn btn-primary">Save Tax</button>
        </form>
    </div>
</div>
@endsection
