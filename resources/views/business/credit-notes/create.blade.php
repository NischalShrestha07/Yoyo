@extends('layouts.app')

@section('title', 'Add Credit Note')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Add Credit Note</h4>
    </div>
    <a href="{{ route('business.credit-notes.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('business.credit-notes.store', $business) }}">
            @csrf
            <div class="mb-3"><label class="form-label">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->full_name }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Sale (Optional)</label>
                <select name="sale_id" class="form-select">
                    <option value="">None</option>
                    @foreach($sales as $sale)<option value="{{ $sale->id }}">{{ $sale->invoice_number }} - {{ number_format($sale->total_amount,2) }}</option>@endforeach
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Date *</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="col-md-6"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            </div>
            <div class="mb-3"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary">Create Credit Note</button>
        </form>
    </div>
</div>
@endsection
