@extends('layouts.app')

@section('title', 'Debit Notes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Debit Notes</h4>
        <p class="text-muted mb-0">Vendor debit notes</p>
    </div>
    <a href="{{ route('business.debit-notes.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Debit Note</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Debit Note #</th><th>Date</th><th>Vendor</th><th>Bill</th><th>Amount</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($debitNotes as $dn)
                        <tr>
                            <td class="fw-medium">{{ $dn->debit_note_number }}</td>
                            <td>{{ $dn->date->format('d M Y') }}</td>
                            <td>{{ $dn->vendor->name }}</td>
                            <td>{{ $dn->bill->bill_number ?? '-' }}</td>
                            <td>{{ number_format($dn->amount, 2) }}</td>
                            <td><span class="badge bg-success">{{ $dn->status }}</span></td>
                            <td class="text-end"><a href="{{ route('business.debit-notes.show', [$business, $dn]) }}" class="btn btn-sm btn-light">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No debit notes found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($debitNotes->hasPages())<div class="card-footer">{{ $debitNotes->links() }}</div>@endif
</div>
@endsection
