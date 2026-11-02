@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mt-2">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-building"></i> My Businesses</span>
            </div>
            <div class="card-body">
                @if($businesses->count() > 0)
                    <div class="row">
                        @foreach($businesses as $business)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $business->name }}</h5>
                                        <p class="card-text text-muted">{{ ucfirst($business->type ?? 'Business') }}</p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('business.pos.index', $business) }}" class="btn btn-sm btn-success"><i class="bi bi-cart-check"></i> POS</a>
                                            <a href="{{ route('business.sales.index', $business) }}" class="btn btn-sm btn-primary"><i class="bi bi-receipt"></i> Sales</a>
                                            <a href="{{ route('business.inventory.index', $business) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-boxes"></i> Inventory</a>
                                            <a href="{{ route('business.products.index', $business) }}" class="btn btn-sm btn-secondary"><i class="bi bi-box"></i> Products</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-building fs-1 text-muted"></i>
                        <p class="text-muted mt-2">You are not a member of any business yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-lightning-charge"></i> Quick Actions</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(isset($businesses) && $businesses->count() > 0)
                        <a href="{{ route('business.pos.index', $businesses->first()) }}" class="btn btn-success"><i class="bi bi-cart-plus"></i> Open POS</a>
                        <a href="{{ route('business.products.create', $businesses->first()) }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Product</a>
                        <a href="{{ route('business.customers.create', $businesses->first()) }}" class="btn btn-info text-white"><i class="bi bi-person-plus"></i> Add Customer</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
