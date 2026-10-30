@extends('layouts.app')

@section('title', 'Edit stock location')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Edit {{ $stockLocation->name }}</h5>
    <form method="POST" action="{{ route('business.stock-locations.update', [$business, $stockLocation]) }}" class="row g-3">
        @csrf
        @method('PUT')
        @include('business.stock-locations._form', ['stockLocation' => $stockLocation])
        <div class="col-12">
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('business.stock-locations.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
