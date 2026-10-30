@extends('layouts.app')

@section('title', 'Inventory settings')

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Inventory settings</h5>
        <a href="{{ route('business.inventory_index', $business) }}" class="btn btn-outline-secondary btn-sm">Back to inventory</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.inventory_settings.update', $business) }}" class="row g-3">
        @csrf

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Stock behaviour</h6>
                <div class="form-check form-switch">
                    <input type="hidden" name="inventory_enforce_stock" value="0">
                    <input class="form-check-input" type="checkbox" name="inventory_enforce_stock" value="1"
                           id="enforce_stock" @checked(old('inventory_enforce_stock', $config->enforceStock()))>
                    <label class="form-check-label" for="enforce_stock">Reject stock changes that exceed availability</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="inventory_multiple_locations" value="0">
                    <input class="form-check-input" type="checkbox" name="inventory_multiple_locations" value="1"
                           id="multiple_locations" @checked(old('inventory_multiple_locations', $config->multipleLocations()))>
                    <label class="form-check-label" for="multiple_locations">Enable multiple stock locations and transfers</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="inventory_reservations_enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="inventory_reservations_enabled" value="1"
                           id="reservations_enabled" @checked(old('inventory_reservations_enabled', $config->reservationsEnabled()))>
                    <label class="form-check-label" for="reservations_enabled">Enable stock reservations</label>
                </div>
            </div></div>
        </div>

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Valuation</h6>
                <label class="form-label">Valuation method</label>
                <select name="inventory_valuation_method" class="form-select" required>
                    <option value="weighted_average" @selected(old('inventory_valuation_method', $config->valuationMethod()) === 'weighted_average')>
                        Weighted average
                    </option>
                    <option value="fifo" @selected(old('inventory_valuation_method', $config->valuationMethod()) === 'fifo')>
                        FIFO (from movement ledger)
                    </option>
                </select>
                <div class="form-text">The selected method is stored per business and used by all valuations.</div>
            </div></div>
        </div>

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Tracking</h6>
                <div class="form-check form-switch">
                    <input type="hidden" name="inventory_batch_tracking" value="0">
                    <input class="form-check-input" type="checkbox" name="inventory_batch_tracking" value="1"
                           id="batch_tracking" @checked(old('inventory_batch_tracking', $config->batchTracking()))>
                    <label class="form-check-label" for="batch_tracking">Batch and expiry tracking</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="inventory_serial_tracking" value="0">
                    <input class="form-check-input" type="checkbox" name="inventory_serial_tracking" value="1"
                           id="serial_tracking" @checked(old('inventory_serial_tracking', $config->serialTracking()))>
                    <label class="form-check-label" for="serial_tracking">Serial number tracking</label>
                </div>
            </div></div>
        </div>

        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h6>Product capabilities</h6>
                <div class="form-check form-switch">
                    <input type="hidden" name="products_variants_enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="products_variants_enabled" value="1"
                           id="variants_enabled" @checked(old('products_variants_enabled', $config->variantsEnabled()))>
                    <label class="form-check-label" for="variants_enabled">Enable product variants</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="products_custom_attributes_enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="products_custom_attributes_enabled" value="1"
                           id="custom_attributes_enabled" @checked(old('products_custom_attributes_enabled', $config->customAttributesEnabled()))>
                    <label class="form-check-label" for="custom_attributes_enabled">Enable custom product attributes</label>
                </div>
            </div></div>
        </div>

        <div class="col-12">
            <label class="form-label">Custom attribute schema (JSON)</label>
            <textarea name="products_custom_attribute_schema" rows="5" class="form-control font-monospace">{{ old('products_custom_attribute_schema', json_encode($config->customAttributeSchema(), JSON_PRETTY_PRINT)) }}</textarea>
            <div class="form-text">
                Example: <code>[{"key":"colour","label":"Colour","type":"select","options":["Red","Blue"],"required":false}]</code>.
                Supported types: string, number, boolean, date, select. No code is executed.
            </div>
        </div>

        <div class="col-12">
            <button class="btn btn-primary">Save settings</button>
        </div>
    </form>
@endsection
