@extends('layouts.app')

@section('title', 'Business Settings')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header"><i class="bi bi-gear"></i> Business Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.settings.update', $business) }}">
            @csrf
            @method('PUT')
            <h6>Sales Settings</h6>
            <div class="mb-2">
                <label class="form-label">Tax Rate (%)</label>
                <input type="number" step="0.01" name="sales.tax_rate" class="form-control" value="{{ $settings['sales.tax_rate'] ?? 0 }}">
            </div>
            <div class="form-check mb-2">
                <input type="hidden" name="sales.allow_partial_payments" value="0">
                <input type="checkbox" name="sales.allow_partial_payments" value="1" class="form-check-input" {{ ($settings['sales.allow_partial_payments'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Allow Partial Payments</label>
            </div>
            <hr>
            <h6>Inventory Settings</h6>
            <div class="form-check mb-2">
                <input type="hidden" name="inventory.enforce_stock" value="0">
                <input type="checkbox" name="inventory.enforce_stock" value="1" class="form-check-input" {{ ($settings['inventory.enforce_stock'] ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Enforce Stock</label>
            </div>
            <div class="mb-2">
                <label class="form-label">Valuation Method</label>
                <select name="inventory.valuation_method" class="form-select">
                    <option value="weighted_average" {{ ($settings['inventory.valuation_method'] ?? '') == 'weighted_average' ? 'selected' : '' }}>Weighted Average</option>
                    <option value="fifo" {{ ($settings['inventory.valuation_method'] ?? '') == 'fifo' ? 'selected' : '' }}>FIFO</option>
                </select>
            </div>
            <hr>
            <h6>Products Settings</h6>
            <div class="form-check mb-2">
                <input type="hidden" name="products.variants_enabled" value="0">
                <input type="checkbox" name="products.variants_enabled" value="1" class="form-check-input" {{ ($settings['products.variants_enabled'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Enable Variants</label>
            </div>
            <button class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
@endsection
