@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-truck"></i> Suppliers</span>
        <a href="{{ route('business.suppliers.create', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Add Supplier</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->email }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>{{ $supplier->is_active ? 'Active' : 'Inactive' }}</td>
                        <td><a href="{{ route('business.suppliers.show', [$business, $supplier]) }}" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $suppliers->links() }}
    </div>
</div>
@endsection
