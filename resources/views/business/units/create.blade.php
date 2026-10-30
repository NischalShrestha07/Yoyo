@extends('layouts.app')

@section('title', 'New unit')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>New unit of measurement</h5>
    <form method="POST" action="{{ route('business.units.store', $business) }}" class="row g-3">
        @csrf
        @include('business.units._form', ['unit' => null])
        <div class="col-12">
            <button class="btn btn-primary">Create unit</button>
            <a href="{{ route('business.units.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
