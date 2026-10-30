@extends('layouts.app')

@section('title', 'Edit variant')

@section('content')
    @include('business._nav', ['business' => $business])

    @include('business.products.variants.form', [
        'business' => $business,
        'product' => $product,
        'variant' => $variant,
    ])
@endsection
