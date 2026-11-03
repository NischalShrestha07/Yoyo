@extends('layouts.app')

@section('title', 'Add Debit Note')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Add Debit Note</h4>
    </div>
    <a href="{{ route('business.debit-notes.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('business.debit-notes.store', $business) }}">
            @csrf
            <div class="mb-3"><label class="form-label">Vendor *</label>
                <select name="vendor_id" class="form-select" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->name }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Bill (Optional)</label>
                <select name="bill_id" class="form-select">
                    <option value="">None</option>
                    @foreach($bills as $bill)<option value="{{ $bill->id }}">{{ $bill->bill_number }}</option>@endforeach
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Date *</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="col-md-6"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            </div>
            <div class="mb-3"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary">Create Debit Note</button>
        </form>
    </div>
</div>
@endsection
