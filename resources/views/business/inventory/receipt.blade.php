@extends('layouts.app')

@section('title', 'Receive stock')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Receive stock</h5>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.inventory.receipt', $business) }}" class="row g-3">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
                <option value="">Select product…</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Location</label>
            <select name="stock_location_id" class="form-select" required>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((int) old('stock_location_id') === $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Quantity</label>
            <input type="number" min="1" name="quantity" class="form-control" required value="{{ old('quantity') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Unit cost</label>
            <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}"
                   placeholder="Purchase order, invoice…">
        </div>
        <div class="col-md-4">
            <label class="form-label">Variant ID (optional)</label>
            <input type="number" min="1" name="product_variant_id" class="form-control"
                   value="{{ old('product_variant_id') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
        </div>

        @if ($config->batchTracking())
            <div class="col-12"><hr><h6>Batch tracking</h6></div>
            <div class="col-md-4">
                <label class="form-label">Batch number</label>
                <input type="text" name="batch_info[batch_number]" class="form-control"
                       value="{{ old('batch_info.batch_number') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Expiry date</label>
                <input type="date" name="batch_info[expiry_date]" class="form-control"
                       value="{{ old('batch_info.expiry_date') }}" required>
            </div>
        @endif

        @if ($config->serialTracking())
            <div class="col-12"><hr><h6>Serial tracking</h6></div>
            <div class="col-md-8">
                <label class="form-label">Serial numbers (one per line)</label>
                <textarea name="serial_info_text" rows="4" class="form-control" required>{{ old('serial_info_text') }}</textarea>
                <div class="form-text">Exactly one serial per received unit.</div>
            </div>
        @endif

        <div class="col-12">
            <button class="btn btn-primary">Receive stock</button>
            <a href="{{ route('business.inventory.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
