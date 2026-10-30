@extends('layouts.app')

@section('title', 'New category')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>New category</h5>
    <form method="POST" action="{{ route('business.categories.store', $business) }}" class="row g-3">
        @csrf
        @include('business.categories._form', ['category' => null, 'parents' => $parents])
        <div class="col-12">
            <button class="btn btn-primary">Create category</button>
            <a href="{{ route('business.categories.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
