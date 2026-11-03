@extends('layouts.app')

@section('title', 'Accounting')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Accounting Dashboard</h4>
        <p class="text-muted mb-0">Financial overview</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('business.accounting.chart-of-accounts', $business) }}" class="btn btn-light">Chart of Accounts</a>
        <a href="{{ route('business.accounting.journal-entries', $business) }}" class="btn btn-primary">Journal Entries</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach(['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expenses'] as $type => $label)
        <div class="col-md-4 col-lg">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">{{ $label }}</h6>
                    <h4 class="mb-0">{{ $accounts->get($type)?->count() ?? 0 }}</h4>
                    <small class="text-muted">Accounts</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Journal Entries</span>
        <a href="{{ route('business.accounting.journal-entries', $business) }}" class="btn btn-sm btn-light">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Entry #</th><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recentEntries as $entry)
                        <tr>
                            <td class="fw-medium">{{ $entry->entry_number }}</td>
                            <td>{{ $entry->entry_date->format('d M Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($entry->description, 50) }}</td>
                            <td>{{ number_format($entry->total_debit, 2) }}</td>
                            <td>{{ number_format($entry->total_credit, 2) }}</td>
                            <td><span class="badge bg-success">{{ $entry->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No journal entries yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
