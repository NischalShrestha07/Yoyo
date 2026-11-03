@extends('layouts.app')

@section('title', 'Credit Note '.$creditNote->credit_note_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Credit Note {{ $creditNote->credit_note_number }}</h4>
    </div>
    <a href="{{ route('business.credit-notes.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <p><strong>Date:</strong> {{ $creditNote->date->format('d M Y') }}</p>
        <p><strong>Customer:</strong> {{ $creditNote->customer->full_name ?? '-' }}</p>
        <p><strong>Sale:</strong> {{ $creditNote->sale->invoice_number ?? '-' }}</p>
        <p><strong>Amount:</strong> {{ number_format($creditNote->amount, 2) }}</p>
        <p><strong>Status:</strong> <span class="badge bg-success">{{ $creditNote->status }}</span></p>
        <p><strong>Reason:</strong> {{ $creditNote->reason }}</p>
    </div>
</div>
@endsection
