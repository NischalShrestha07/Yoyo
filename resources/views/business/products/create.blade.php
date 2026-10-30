@extends('layouts.app')

@section('title', 'New product')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>New product</h5>

    <form method="POST" action="{{ route('business.products.store', $business) }}" enctype="multipart/form-data" class="row g-3">
        @csrf

        @include('business.products._form', [
            'product' => null,
            'categories' => $categories,
            'units' => $units,
            'config' => $config,
        ])

        <div class="col-12">
            <button class="btn btn-primary">Create product</button>
            <a href="{{ route('business.products.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
