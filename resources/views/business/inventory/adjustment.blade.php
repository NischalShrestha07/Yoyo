@extends('layouts.app')

@section('title', 'Stock adjustment')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Stock adjustment</h5>
    <p class="text-muted">Use a negative quantity to remove stock. Every adjustment records the actor, reason and note.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.inventory.adjustment', $business) }}" class="row g-3">
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
            <input type="number" name="quantity" class="form-control" required value="{{ old('quantity') }}"
                   placeholder="-5">
        </div>
        <div class="col-md-2">
            <label class="form-label">Unit cost</label>
            <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Reason</label>
            <select name="reason" class="form-select" required>
                @foreach (['adjustment', 'damage', 'expired', 'theft', 'other'] as $reason)
                    <option value="{{ $reason }}" @selected(old('reason', 'adjustment') === $reason)>
                        {{ ucfirst($reason) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Notes (required)</label>
            <input type="text" name="notes" class="form-control" required value="{{ old('notes') }}">
        </div>

        @if ($config->batchTracking())
            <div class="col-md-4">
                <label class="form-label">Batch number (positive adjustments)</label>
                <input type="text" name="batch_info[batch_number]" class="form-control"
                       value="{{ old('batch_info.batch_number') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Expiry date</label>
                <input type="date" name="batch_info[expiry_date]" class="form-control"
                       value="{{ old('batch_info.expiry_date') }}">
            </div>
        @endif

        @if ($config->serialTracking())
            <div class="col-md-8">
                <label class="form-label">Serial numbers (positive adjustments, one per line)</label>
                <textarea name="serial_info_text" rows="3" class="form-control">{{ old('serial_info_text') }}</textarea>
            </div>
        @endif

        <div class="col-12">
            <button class="btn btn-primary">Record adjustment</button>
            <a href="{{ route('business.inventory.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
