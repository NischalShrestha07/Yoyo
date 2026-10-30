@extends('layouts.app')

@section('title', 'New service')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>New service</h5>
    <form method="POST" action="{{ route('business.services.store', $business) }}" class="row g-3">
        @csrf
        @include('business.services._form', ['service' => null, 'categories' => $categories])
        <div class="col-12">
            <button class="btn btn-primary">Create service</button>
            <a href="{{ route('business.services.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
