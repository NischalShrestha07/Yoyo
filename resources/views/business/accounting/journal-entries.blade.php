@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Journal Entries</h4>
        <p class="text-muted mb-0">All transactions</p>
    </div>
    <a href="{{ route('business.accounting.dashboard', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Entry #</th><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Status</th><th>By</th></tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td class="fw-medium">{{ $entry->entry_number }}</td>
                            <td>{{ $entry->entry_date->format('d M Y') }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ number_format($entry->total_debit, 2) }}</td>
                            <td>{{ number_format($entry->total_credit, 2) }}</td>
                            <td><span class="badge bg-success">{{ $entry->status }}</span></td>
                            <td>{{ $entry->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No entries found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($entries->hasPages())
        <div class="card-footer">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
