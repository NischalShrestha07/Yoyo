@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">{{ $project->name }}</h4>
        <p class="text-muted mb-0">{{ $project->project_code }}</p>
    </div>
    <a href="{{ route('business.projects.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Project Details</div>
            <div class="card-body">
                <p>{{ $project->description ?: 'No description' }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Info</div>
            <div class="card-body">
                <p class="mb-2"><strong>Customer:</strong> {{ $project->customer->full_name ?? '-' }}</p>
                <p class="mb-2"><strong>Budget:</strong> {{ number_format($project->budget, 2) }}</p>
                <p class="mb-2"><strong>Status:</strong> <span class="badge bg-info">{{ $project->status }}</span></p>
                <p class="mb-2"><strong>Priority:</strong> {{ $project->priority }}</p>
                <p class="mb-2"><strong>Start Date:</strong> {{ $project->start_date?->format('d M Y') ?? '-' }}</p>
                <p class="mb-0"><strong>End Date:</strong> {{ $project->end_date?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
