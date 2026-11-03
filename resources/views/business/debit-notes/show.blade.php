@extends('layouts.app')

@section('title', 'Debit Note '.$debitNote->debit_note_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Debit Note {{ $debitNote->debit_note_number }}</h4>
    </div>
    <a href="{{ route('business.debit-notes.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-6">
    <div class="card-body">
        <p><strong>Date:</strong> {{ $debitNote->date->format('d M Y') }}</p>
        <p><strong>Vendor:</strong> {{ $debitNote->vendor->name }}</p>
        <p><strong>Bill:</strong> {{ $debitNote->bill->bill_number ?? '-' }}</p>
        <p><strong>Amount:</strong> {{ number_format($debitNote->amount, 2) }}</p>
        <p><strong>Status:</strong> <span class="badge bg-success">{{ $debitNote->status }}</span></p>
        <p><strong>Reason:</strong> {{ $debitNote->reason }}</p>
    </div>
</div>
@endsection
