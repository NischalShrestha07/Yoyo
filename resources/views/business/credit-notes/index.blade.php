@extends('layouts.app')

@section('title', 'Credit Notes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Credit Notes</h4>
        <p class="text-muted mb-0">Customer credit notes</p>
    </div>
    <a href="{{ route('business.credit-notes.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Credit Note</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Credit Note #</th><th>Date</th><th>Customer</th><th>Sale</th><th>Amount</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($creditNotes as $cn)
                        <tr>
                            <td class="fw-medium">{{ $cn->credit_note_number }}</td>
                            <td>{{ $cn->date->format('d M Y') }}</td>
                            <td>{{ $cn->customer->full_name ?? '-' }}</td>
                            <td>{{ $cn->sale->invoice_number ?? '-' }}</td>
                            <td>{{ number_format($cn->amount, 2) }}</td>
                            <td><span class="badge bg-success">{{ $cn->status }}</span></td>
                            <td class="text-end"><a href="{{ route('business.credit-notes.show', [$business, $cn]) }}" class="btn btn-sm btn-light">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No credit notes found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($creditNotes->hasPages())<div class="card-footer">{{ $creditNotes->links() }}</div>@endif
</div>
@endsection
