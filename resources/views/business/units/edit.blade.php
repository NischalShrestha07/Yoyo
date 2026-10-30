@extends('layouts.app')

@section('title', 'Edit unit')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Edit {{ $unit->name }}</h5>
    <form method="POST" action="{{ route('business.units.update', [$business, $unit]) }}" class="row g-3">
        @csrf
        @method('PUT')
        @include('business.units._form', ['unit' => $unit])
        <div class="col-12">
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('business.units.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
