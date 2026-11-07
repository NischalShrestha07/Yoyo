@extends('layouts.app')

@section('title', 'Create Recurring Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Create Recurring Invoice</h4>
    </div>
    <a href="{{ route('business.recurring-invoices.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('business.recurring-invoices.store', $business) }}">
            @csrf
            <div class="mb-3"><label class="form-label">Customer *</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->full_name }}</option>@endforeach
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Frequency *</label>
                    <select name="frequency" class="form-select" required>
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                        <option value="daily">Daily</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            </div>
            <div class="mb-3"><label class="form-label">Amount *</label><input type="number" step="0.01" name="total_amount" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary">Create Recurring Invoice</button>
        </form>
    </div>
</div>
@endsection
