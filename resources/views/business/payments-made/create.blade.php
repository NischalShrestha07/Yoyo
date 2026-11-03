@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Record Payment</h4>
    </div>
    <a href="{{ route('business.payments-made.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('business.payments-made.store', $business) }}">
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
                    @foreach($bills as $bill)<option value="{{ $bill->id }}">{{ $bill->bill_number }} - Due: {{ number_format($bill->amount_due,2) }}</option>@endforeach
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Payment Date *</label><input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="col-md-6"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control"></div>
            </div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary">Record Payment</button>
        </form>
    </div>
</div>
@endsection
