@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Dashboard</h4>
        <p class="text-muted mb-0">Overview of your businesses</p>
    </div>
    <a href="{{ route('businesses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create Business
    </a>
</div>

@if($businesses->isEmpty())
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="bi bi-shop display-1 text-muted"></i>
            <h5 class="mt-3">No Businesses Yet</h5>
            <p class="text-muted">Create your first business to start managing sales, purchases, and inventory</p>
            <a href="{{ route('businesses.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-lg"></i> Create Your First Business
            </a>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach($businesses as $business)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-shop fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-semibold">{{ $business->name }}</h6>
                                <small class="text-muted">{{ $business->type ?? 'Retail' }}</small>
                            </div>
                        </div>
                        <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($business->description ?? 'No description', 80) }}</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('business.pos.index', $business) }}" class="btn btn-primary">
                                <i class="bi bi-cart-check"></i> Open POS
                            </a>
                            <div class="btn-group">
                                <a href="{{ route('business.reports.index', $business) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-bar-chart"></i> Reports
                                </a>
                                <a href="{{ route('business.products.index', $business) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-box"></i> Products
                                </a>
                                <a href="{{ route('business.settings.index', $business) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
.hover-shadow {
    transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out;
}
.hover-shadow:hover {
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
</style>
@endsection
