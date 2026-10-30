@extends('layouts.app')

@section('title', 'Edit product')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Edit {{ $product->name }}</h5>

    <form method="POST" action="{{ route('business.products.update', [$business, $product]) }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('PUT')

        @include('business.products._form', [
            'product' => $product,
            'categories' => $categories,
            'units' => $units,
            'config' => $config,
        ])

        <div class="col-12">
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('business.products.show', [$business, $product]) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
