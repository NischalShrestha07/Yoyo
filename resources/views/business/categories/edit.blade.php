@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
    @include('business._nav', ['business' => $business])

    <h5>Edit {{ $category->name }}</h5>
    <form method="POST" action="{{ route('business.categories.update', [$business, $category]) }}" class="row g-3">
        @csrf
        @method('PUT')
        @include('business.categories._form', ['category' => $category, 'parents' => $parents])
        <div class="col-12">
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('business.categories.index', $business) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
