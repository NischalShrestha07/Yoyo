@extends('layouts.app')

@section('title', 'Stock return')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Stock return</h5>
    <p class="text-muted">Returns add stock back and reference the original sale or cancellation document.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.inventory.return', $business) }}" class="row g-3">
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
            <label class="form-label">Variant ID (optional)</label>
            <input type="number" min="1" name="product_variant_id" class="form-control"
                   value="{{ old('product_variant_id') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Original sale / cancellation reference</label>
            <input type="text" name="reference" class="form-control" required value="{{ old('reference') }}">
        </div>
        <div class="col-md-8">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Record return</button>
            <a href="{{ route('business.inventory.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
