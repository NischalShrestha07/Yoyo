@extends('layouts.app')

@section('title', 'Edit service')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Edit {{ $service->name }}</h5>
    <form method="POST" action="{{ route('business.services.update', [$business, $service]) }}" class="row g-3">
        @csrf
        @method('PUT')
        @include('business.services._form', ['service' => $service, 'categories' => $categories])
        <div class="col-12">
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('business.services.show', [$business, $service]) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
