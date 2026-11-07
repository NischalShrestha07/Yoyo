@extends('layouts.app')

@section('title', 'Add Project')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Add Project</h4>
    </div>
    <a href="{{ route('business.projects.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card col-lg-8">
    <div class="card-body">
        <form method="POST" action="{{ route('business.projects.store', $business) }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Project Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Project Code *</label><input type="text" name="project_code" class="form-control" required></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->full_name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Budget</label><input type="number" step="0.01" name="budget" class="form-control"></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="planning">Planning</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <button class="btn btn-primary">Create Project</button>
        </form>
    </div>
</div>
@endsection
