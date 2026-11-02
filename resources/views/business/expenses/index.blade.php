@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-cash-stack"></i> Expenses - Total: {{ number_format($total, 2) }}</span>
        <a href="{{ route('business.expenses.create', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Add Expense</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Amount</th><th>Notes</th></tr></thead>
            <tbody>
                @foreach($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td>{{ $expense->title }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $expenses->links() }}
    </div>
</div>
@endsection
