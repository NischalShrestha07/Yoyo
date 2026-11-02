@extends('layouts.app')

@section('title', 'Add Expense')

@section('content')
@include('business._nav')
<div class="card col-md-6">
    <div class="card-header">Add Expense</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.expenses.store', $business) }}">
            @csrf
            <div class="mb-2"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="general" required></div>
            <div class="mb-2"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
