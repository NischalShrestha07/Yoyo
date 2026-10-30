@extends('layouts.app')

@section('title', $category->name)

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1">{{ $category->name }}</h5>
            <div class="text-muted">
                {{ $category->parent?->name ?? 'Top level' }}
                · <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                    {{ $category->is_active ? 'Active' : 'Archived' }}
                </span>
            </div>
        </div>
        <a href="{{ route('business.categories.edit', [$business, $category]) }}"
           class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>

    @if ($category->description)
        <p>{{ $category->description }}</p>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <h6>Products ({{ $category->products->count() }})</h6>
            <ul class="list-group">
                @forelse ($category->products as $product)
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{ route('business.products.show', [$business, $product]) }}">{{ $product->name }}</a>
                        <span class="badge bg-light text-dark border">{{ $product->sku }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No products.</li>
                @endforelse
            </ul>
        </div>
        <div class="col-md-6">
            <h6>Services ({{ $category->services->count() }})</h6>
            <ul class="list-group">
                @forelse ($category->services as $service)
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{ route('business.services.show', [$business, $service]) }}">{{ $service->name }}</a>
                        <span class="badge bg-light text-dark border">{{ $service->code }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No services.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if ($category->children->isNotEmpty())
        <h6 class="mt-4">Subcategories</h6>
        <ul class="list-group">
            @foreach ($category->children as $child)
                <li class="list-group-item">
                    <a href="{{ route('business.categories.show', [$business, $child]) }}">{{ $child->name }}</a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
