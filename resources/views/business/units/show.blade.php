@extends('layouts.app')

@section('title', $unit->name)

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1">{{ $unit->name }}</h5>
            <div class="text-muted">
                <code>{{ $unit->code }}</code>
                · <span class="badge bg-{{ $unit->is_active ? 'success' : 'secondary' }}">
                    {{ $unit->is_active ? 'Active' : 'Archived' }}
                </span>
            </div>
        </div>
        <a href="{{ route('business.units.edit', [$business, $unit]) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>

    @if ($unit->description)
        <p>{{ $unit->description }}</p>
    @endif

    <h6>Products using this unit ({{ $unit->products->count() }})</h6>
    <ul class="list-group">
        @forelse ($unit->products as $product)
            <li class="list-group-item d-flex justify-content-between">
                <a href="{{ route('business.products.show', [$business, $product]) }}">{{ $product->name }}</a>
                <span class="badge bg-light text-dark border">{{ $product->sku }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted">No products use this unit.</li>
        @endforelse
    </ul>
@endsection
