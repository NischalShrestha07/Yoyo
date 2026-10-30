@extends('layouts.app')

@section('title', 'Product variants')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Variants — {{ $product->name }}</h5>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $variant->exists
              ? route('business.products.variants.update', [$business, $product, $variant])
              : route('business.products.variants.store', [$business, $product]) }}"
          class="row g-3">
        @csrf
        @if ($variant->exists)
            @method('PUT')
        @endif

        <div class="col-md-6">
            <label class="form-label">Variant name</label>
            <input type="text" name="name" class="form-control" required
                   value="{{ old('name', $variant->name) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" required
                   value="{{ old('sku', $variant->sku) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Barcode</label>
            <input type="text" name="barcode" class="form-control"
                   value="{{ old('barcode', $variant->barcode) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Purchase cost</label>
            <input type="number" step="0.01" min="0" name="purchase_cost" class="form-control" required
                   value="{{ old('purchase_cost', $variant->purchase_cost ?? '0.00') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Selling price</label>
            <input type="number" step="0.01" min="0" name="selling_price" class="form-control" required
                   value="{{ old('selling_price', $variant->selling_price ?? '0.00') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Active</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $variant->is_active ?? true))>
            </div>
        </div>

        <div class="col-12">
            <button class="btn btn-primary">{{ $variant->exists ? 'Save variant' : 'Create variant' }}</button>
            <a href="{{ route('business.products.show', [$business, $product]) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
