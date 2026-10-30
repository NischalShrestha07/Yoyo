@extends('layouts.app')

@section('title', 'New stock location')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>New stock location</h5>
    <form method="POST" action="{{ route('business.stock-locations.store', $business) }}" class="row g-3">
        @csrf
        @include('business.stock-locations._form', ['stockLocation' => null])
        <div class="col-12">
            <button class="btn btn-primary">Create location</button>
            <a href="{{ route('business.stock-locations.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
