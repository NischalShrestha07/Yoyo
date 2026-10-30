@extends('layouts.app')

@section('title', 'New variant')

@section('content')
    @include('business._nav', ['business' => $business])

    @include('business.products.variants.form', [
        'business' => $business,
        'product' => $product,
        'variant' => new \App\Models\ProductVariant(),
    ])
@endsection
