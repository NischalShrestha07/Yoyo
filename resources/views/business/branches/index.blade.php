@extends('layouts.app')

@section('title', 'Branches')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-diagram-3"></i> Branches</span>
        <a href="{{ route('business.branches.create', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Add Branch</a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Code</th><th>Phone</th><th>Default</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($branches as $branch)
                    <tr>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->code }}</td>
                        <td>{{ $branch->phone }}</td>
                        <td>{{ $branch->is_default ? 'Yes' : 'No' }}</td>
                        <td>{{ $branch->is_active ? 'Active' : 'Inactive' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $branches->links() }}
    </div>
</div>
@endsection
