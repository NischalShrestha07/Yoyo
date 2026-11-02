@extends('layouts.app')

@section('title', 'Customers')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Customers</span>
        <a href="{{ route('business.customers.create', $business) }}" class="btn btn-sm btn-primary">Add Customer</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search customers" value="{{ request('search') }}">
        </form>
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $customer->full_name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->is_active ? 'Active' : 'Inactive' }}</td>
                        <td><a href="{{ route('business.customers.show', [$business, $customer]) }}" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</div>
@endsection
