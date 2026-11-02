@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-file-earmark-text"></i> Quotations</span>
        <a href="{{ route('business.quotations.create', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> New Quotation</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Quote #</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($quotations as $quote)
                    <tr>
                        <td>{{ $quote->quote_number }}</td>
                        <td>{{ $quote->customer->full_name ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $quote->status }}</span></td>
                        <td>{{ number_format($quote->total_amount, 2) }}</td>
                        <td>{{ $quote->created_at->format('Y-m-d') }}</td>
                        <td><a href="{{ route('business.quotations.show', [$business, $quote]) }}" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $quotations->links() }}
    </div>
</div>
@endsection
