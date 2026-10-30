@extends('layouts.app')

@section('title', 'Transfer stock')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Transfer stock</h5>

    @unless ($config->multipleLocations())
        <div class="alert alert-info">
            This business is configured for a single stock location. Enable multiple locations in
            <a href="{{ route('business.inventory.settings', $business) }}">inventory settings</a> to use transfers.
        </div>
    @else
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('business.inventory.transfer', $business) }}" class="row g-3">
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
                <label class="form-label">From location</label>
                <select name="from_location_id" class="form-select" required>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((int) old('from_location_id') === $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">To location</label>
                <select name="to_location_id" class="form-select" required>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((int) old('to_location_id') === $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity</label>
                <input type="number" min="1" name="quantity" class="form-control" required value="{{ old('quantity') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Variant ID (optional)</label>
                <input type="number" min="1" name="product_variant_id" class="form-control"
                       value="{{ old('product_variant_id') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Transfer stock</button>
                <a href="{{ route('business.inventory.index', $business) }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    @endunless
@endsection
