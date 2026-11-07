@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Projects</h4>
        <p class="text-muted mb-0">Manage projects & jobs</p>
    </div>
    <a href="{{ route('business.projects.create', $business) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Project</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Project</th><th>Code</th><th>Customer</th><th>Budget</th><th>Status</th><th>Priority</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td class="fw-medium">{{ $project->name }}</td>
                            <td>{{ $project->project_code }}</td>
                            <td>{{ $project->customer->full_name ?? '-' }}</td>
                            <td>{{ number_format($project->budget, 2) }}</td>
                            <td><span class="badge bg-info">{{ $project->status }}</span></td>
                            <td><span class="badge bg-{{ $project->priority === 'high' || $project->priority === 'urgent' ? 'danger' : 'secondary' }}">{{ $project->priority }}</span></td>
                            <td class="text-end"><a href="{{ route('business.projects.show', [$business, $project]) }}" class="btn btn-sm btn-light">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No projects found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($projects->hasPages())<div class="card-footer">{{ $projects->links() }}</div>@endif
</div>
@endsection
