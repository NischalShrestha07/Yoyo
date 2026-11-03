@extends('layouts.app')

@section('title', 'Cash Registers')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-cash"></i> Cash Registers</span>
        <a href="{{ route('business.cash-registers.create', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Open Register</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Status</th><th>Opening</th><th>Current</th><th>Opened By</th><th>Opened At</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($registers as $register)
                    <tr>
                        <td>{{ $register->name }}</td>
                        <td><span class="badge bg-{{ $register->status === 'open' ? 'success' : 'secondary' }}">{{ $register->status }}</span></td>
                        <td>{{ number_format($register->opening_balance, 2) }}</td>
                        <td>{{ number_format($register->current_balance, 2) }}</td>
                        <td>{{ $register->user->name ?? '-' }}</td>
                        <td>{{ $register->opened_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($register->status === 'open')
                                <form method="POST" action="{{ route('business.cash-registers.close', [$business, $register]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">Close</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $registers->links() }}
    </div>
</div>
@endsection
